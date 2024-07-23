<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\BouncedEmail;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:parse-postfix-logs',
    description: 'Load log bounce email',
    hidden: false,
    aliases: ['app:parse-postfix-logs']
)]
class ParsePostfixLogsCommand extends Command
{
    protected static $defaultName = 'app:parse-postfix-logs';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Parse Postfix logs and insert bounced emails into the database.')
            ->addArgument('logfile', InputArgument::REQUIRED, 'Path to the Postfix log file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logFile = $input->getArgument('logfile');
        $newLogContent = '';

        if (file_exists($logFile)) {
            $handle = fopen($logFile, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match('/^(\w+\s+\d+\s+\d+:\d+:\d+)\s+\w+\s+postfix\/smtp\[\d+\]:\s+\w+:\s+to=<([^>]+)>,.*status=deferred\s+\((.*)\)$/', $line, $matches)) {
                        $date = $matches[1];
                        $email = $matches[2];
                        $reason = $matches[3];


                        // Busca no banco de dados para ver se o registro já existe
                        $existingEmailLog = $this->entityManager->getRepository(BouncedEmail::class)->findOneBy(['email' => $email]);

                        if ($existingEmailLog) {
                            // Atualiza o registro existente
                            $existingEmailLog->setDate(new \DateTime($date));
                            $existingEmailLog->setReason($reason);
                            $this->entityManager->flush();
                        } else {
                            // Cria uma nova entidade e persiste no banco de dados
                            $bouncedEmail = new BouncedEmail();
                            $bouncedEmail->setDateTime(new \DateTime($date));
                            $bouncedEmail->setEmail($email);
                            $bouncedEmail->setStatus('Deferred');
                            $bouncedEmail->setReason($reason);
                            $this->entityManager->persist($bouncedEmail);
                            $this->entityManager->flush();
                        }

                    } else {
                        $newLogContent .= $line;
                    }
                }

                // Salva as alterações no banco de dados
                $this->entityManager->flush();

                // Fecha o arquivo de log
                fclose($handle);

                // Reescreve o arquivo de log sem as linhas "deferred"
                file_put_contents($logFile, $newLogContent);

                $output->writeln('Log file processed, deferred entries removed, and log file updated.');
            } else {
                $output->writeln('Error opening log file.');
                return Command::FAILURE;
            }
        } else {
            $output->writeln('Log file does not exist.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;

    }
}