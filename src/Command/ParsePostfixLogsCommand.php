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
    name: 'ParsePostfixLogs',
    description: 'Add a short description for your command',
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
        $fileHandle = fopen($logFile, 'r');

        if ($fileHandle) {
            while (($line = fgets($fileHandle)) !== false) {
                $matches = [];
                if (preg_match('/^(\w+\s+\d+\s+\d+:\d+:\d+).*to=<([^>]+)>.*status=deferred.*\((.*)\)/i', $line, $matches)) {
                    $dateTime = \DateTime::createFromFormat('M d H:i:s', $matches[1]);
                    $dateTime->setDate(date('Y'), $dateTime->format('m'), $dateTime->format('d'));
                    $email = $matches[2];
                    $reason = $matches[3];

                    $bouncedEmail = new BouncedEmail();
                    $bouncedEmail->setDateTime($dateTime);
                    $bouncedEmail->setEmail($email);
                    $bouncedEmail->setReason($reason);

                    $this->entityManager->persist($bouncedEmail);
                }
            }
            fclose($fileHandle);

            $this->entityManager->flush();

            // Limpar o arquivo de log
            file_put_contents($logFile, '');

            // Remover mensagens deferred da fila do Postfix
            exec('postsuper -d ALL deferred');

            $output->writeln('Bounced emails have been parsed and inserted into the database.');
            $output->writeln('The log file has been cleared and deferred messages have been removed from the Postfix queue.');
            return Command::SUCCESS;
        } else {
            $output->writeln('Error opening the log file.');
            return Command::FAILURE;
        }
    }
}
