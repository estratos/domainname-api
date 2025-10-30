<?php

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Service\DomainNameApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:balance',
    description: 'Check account balance'
)]
class DomainBalanceCommand extends Command
{
    public function __construct(
        private DomainNameApiClient $apiClient
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Checking account balance...');

        try {
            $balance = $this->apiClient->GetCurrentBalance();
            
            if (isset($balance['Balance'])) {
                $io->success(sprintf(
                    'Current balance: %s %s',
                    $balance['Balance'],
                    $balance['Currency'] ?? 'USD'
                ));
            } else {
                $io->error('Failed to retrieve balance information');
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error checking balance: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
