<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Application\UseCase\GetBalanceUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:balance',
    description: 'Check account balance'
)]
class GetBalanceCommand extends Command
{
    public function __construct(
        private readonly GetBalanceUseCase $getBalanceUseCase
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Output format (table, json, yaml)',
                'table'
            )
            ->addOption(
                'notify',
                null,
                InputOption::VALUE_NONE,
                'Send notification if balance is low'
            )
            ->addOption(
                'threshold',
                null,
                InputOption::VALUE_OPTIONAL,
                'Low balance threshold for notification',
                '50'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');

        try {
            $io->info('Retrieving account balance...');
            $result = $this->getBalanceUseCase->execute();

            if ($format === 'json') {
                $this->outputJson($io, $result);
            } elseif ($format === 'yaml') {
                $this->outputYaml($io, $result);
            } else {
                $this->outputTable($io, $result);
            }

            // Notificación de saldo bajo
            if ($input->getOption('notify')) {
                $threshold = (float) $input->getOption('threshold');
                if ($result->balance < $threshold) {
                    $io->warning(sprintf(
                        '⚠️  Low balance: %s. Current balance is below threshold of %s',
                        $result->getFormattedBalance(),
                        $threshold
                    ));
                } else {
                    $io->success('Balance is above threshold');
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error checking balance: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function outputTable(SymfonyStyle $io, $result): void
    {
        $io->title('Account Balance');

        $table = new Table($io);
        $table->setHeaders(['Property', 'Value']);
        $table->addRows([
            ['Balance', $result->getFormattedBalance()],
            ['Currency', $result->currency],
            ['Available', $result->availableAmount !== null ? number_format($result->availableAmount, 2) : 'N/A'],
            ['Reserved', $result->reservedAmount !== null ? number_format($result->reservedAmount, 2) : 'N/A'],
            ['Reseller ID', $result->resellerId ?? 'N/A'],
            ['Reseller Name', $result->resellerName ?? 'N/A'],
        ]);
        $table->render();

        $io->newLine();
        $io->success(sprintf('Current balance: %s', $result->getFormattedBalance()));
    }

    private function outputJson(SymfonyStyle $io, $result): void
    {
        $data = [
            'balance' => $result->balance,
            'currency' => $result->currency,
            'availableAmount' => $result->availableAmount,
            'reservedAmount' => $result->reservedAmount,
            'resellerId' => $result->resellerId,
            'resellerName' => $result->resellerName,
        ];
        $io->writeln(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function outputYaml(SymfonyStyle $io, $result): void
    {
        $data = [
            'balance' => $result->balance,
            'currency' => $result->currency,
            'availableAmount' => $result->availableAmount,
            'reservedAmount' => $result->reservedAmount,
            'resellerId' => $result->resellerId,
            'resellerName' => $result->resellerName,
        ];
        
        if (!function_exists('yaml_emit')) {
            $io->warning('YAML extension not installed, falling back to JSON');
            $this->outputJson($io, $result);
            return;
        }

        $io->writeln(yaml_emit($data));
    }
}