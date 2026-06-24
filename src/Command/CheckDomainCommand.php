<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Application\UseCase\CheckDomainUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:check',
    description: 'Check domain availability'
)]
class CheckDomainCommand extends Command
{
    public function __construct(
        private readonly CheckDomainUseCase $checkDomainUseCase
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name to check (e.g., example.com)')
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Output format (table, json, yaml)',
                'table'
            )
            ->addOption(
                'currency',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Currency for price display',
                'USD'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $domain = $input->getArgument('domain');
        $format = $input->getOption('format');

        try {
            $result = $this->checkDomainUseCase->execute($domain);

            if ($format === 'json') {
                $this->outputJson($io, $result);
                return Command::SUCCESS;
            }

            if ($format === 'yaml') {
                $this->outputYaml($io, $result);
                return Command::SUCCESS;
            }

            // Output por defecto en tabla
            $this->outputTable($io, $result);

            // Mensaje de éxito o fracaso
            if ($result->isAvailable()) {
                $io->success(sprintf('✅ Domain "%s" is AVAILABLE!', $domain));
                if ($result->getFormattedPrice()) {
                    $io->note(sprintf('Price: %s', $result->getFormattedPrice()));
                }
            } else {
                $io->error(sprintf('❌ Domain "%s" is NOT available', $domain));
                if ($result->reason) {
                    $io->warning(sprintf('Reason: %s', $result->reason));
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error checking domain: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function outputTable(SymfonyStyle $io, $result): void
    {
        $io->title('Domain Check Result');

        $table = new Table($io);
        $table->setHeaders(['Property', 'Value']);
        $table->addRows([
            ['Domain', $result->domain],
            ['Available', $result->isAvailable() ? '✅ YES' : '❌ NO'],
            ['Price', $result->getFormattedPrice() ?? 'N/A'],
            ['Currency', $result->currency ?? 'N/A'],
            ['Status', $result->status ?? 'N/A'],
            ['Reason', $result->reason ?? 'N/A'],
            ['Registration Period', $result->registrationPeriod ? $result->registrationPeriod . ' year(s)' : 'N/A'],
            ['Is Premium', $result->isPremium ? 'Yes' : 'No'],
        ]);
        $table->render();
    }

    private function outputJson(SymfonyStyle $io, $result): void
    {
        $data = [
            'domain' => $result->domain,
            'available' => $result->available,
            'price' => $result->price,
            'currency' => $result->currency,
            'status' => $result->status,
            'reason' => $result->reason,
            'registrationPeriod' => $result->registrationPeriod,
            'isPremium' => $result->isPremium,
        ];
        $io->writeln(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function outputYaml(SymfonyStyle $io, $result): void
    {
        $data = [
            'domain' => $result->domain,
            'available' => $result->available,
            'price' => $result->price,
            'currency' => $result->currency,
            'status' => $result->status,
            'reason' => $result->reason,
            'registrationPeriod' => $result->registrationPeriod,
            'isPremium' => $result->isPremium,
        ];
        
        if (!function_exists('yaml_emit')) {
            $io->warning('YAML extension not installed, falling back to JSON');
            $this->outputJson($io, $result);
            return;
        }

        $io->writeln(yaml_emit($data));
    }
}