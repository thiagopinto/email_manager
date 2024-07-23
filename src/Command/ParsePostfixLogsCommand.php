<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

        if (!file_exists($logFile)) {
            $output->writeln('Log file does not exist.');
            return Command::FAILURE;
        }

        if (!$handle = fopen($logFile, 'r')) {
            $output->writeln('Error opening log file.');
            return Command::FAILURE;
        }

        while (($line = fgets($handle)) !== false) {
            if ($this->isDeferredLine($line)) {
                list($date, $email, $reason) = $this->parseLogLine($line);

                if ($this->shouldProcessLine($reason)) {
                    $this->processDeferredLine($date, $email, $reason);
                }
            } else {
                $newLogContent .= $line;
            }
        }

        fclose($handle);
        $this->entityManager->flush();
        file_put_contents($logFile, $newLogContent);

        $output->writeln('Log file processed, deferred entries removed, and log file updated.');

        return Command::SUCCESS;
    }

    private function isDeferredLine(string $line): bool
    {
        return preg_match('/^(\w+\s+\d+\s+\d+:\d+:\d+)\s+\w+\s+postfix\/smtp\[\d+\]:\s+\w+:\s+to=<([^>]+)>,.*status=deferred\s+\((.*)\)$/', $line);
    }

    private function parseLogLine(string $line): array
    {
        preg_match('/^(\w+\s+\d+\s+\d+:\d+:\d+)\s+\w+\s+postfix\/smtp\[\d+\]:\s+\w+:\s+to=<([^>]+)>,.*status=deferred\s+\((.*)\)$/', $line, $matches);
        return [$matches[1], $matches[2], $matches[3]];
    }

    private function shouldProcessLine(string $reason): bool
    {
        // Use stripos to ignore case when checking for "Connection timed out"
        return stripos($reason, 'Connection timed out') === false;
    }

    private function processDeferredLine(string $date, string $email, string $reason): void
    {
        $existingEmailLog = $this->entityManager->getRepository(BouncedEmail::class)->findOneBy(['email' => $email]);

        if ($existingEmailLog) {
            $existingEmailLog->setDateTime(new \DateTime($date));
            $existingEmailLog->setReason($reason);
        } else {
            $bouncedEmail = new BouncedEmail();
            $bouncedEmail->setDateTime(new \DateTime($date));
            $bouncedEmail->setEmail($email);
            $bouncedEmail->setStatus('Deferred');
            $bouncedEmail->setReason($reason);
            $this->entityManager->persist($bouncedEmail);
        }
    }
}