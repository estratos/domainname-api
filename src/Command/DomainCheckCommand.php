<?php

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Service\DomainNameApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:check',
    description: 'Check domain availability'
)]
class DomainCheckCommand extends Command
{
    public function __construct(
        private DomainNameApiClient $apiClient
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name to check')
            ->addOption('tld', 't', InputOption::VALUE_OPTIONAL, 'Specific TLD to check', 'com')
            ->addOption('period', 'p', InputOption::VALUE_OPTIONAL, 'Registration period in years', 1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $domain = $input->getArgument('domain');
        $tld = $input->getOption('tld');
        $period = (int) $input->getOption('period');

        $fullDomain = $domain . '.' . $tld;
        $io->info(sprintf('Checking availability for %s...', $fullDomain));

        try {
            $result = $this->apiClient->CheckAvailability(
                [$domain],
                [$tld],
                $period,
                'create'
            );

            if (isset($result[0]['Status'])) {
                $status = $result[0]['Status'];
                $price = $result[0]['Price'] ?? 'N/A';
                $currency = $result[0]['Currency'] ?? 'USD';

                if ($status === 'Available') {
                    $io->success(sprintf(
                        'Domain %s is available for %s %s per year',
                        $fullDomain,
                        $price,
                        $currency
                    ));
                } else {
                    $io->error(sprintf(
                        'Domain %s is not available. Status: %s',
                        $fullDomain,
                        $status
                    ));
                }
            } else {
                $io->error('Invalid response from API');
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error checking domain: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
