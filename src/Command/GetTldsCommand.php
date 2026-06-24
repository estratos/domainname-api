<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Application\UseCase\GetTldsUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:tlds',
    description: 'List available TLDs with prices'
)]
class GetTldsCommand extends Command
{
    public function __construct(
        private readonly GetTldsUseCase $getTldsUseCase
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
                'search',
                's',
                InputOption::VALUE_OPTIONAL,
                'Search TLDs by name (e.g., .com)'
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
        $format = $input->getOption('format');
        $search = $input->getOption('search');
        $currency = $input->getOption('currency');

        try {
            $io->info('Fetching TLDs...');
            $result = $this->getTldsUseCase->execute();

            $tlds = $result->getTlds();

            // Filtrar por búsqueda
            if ($search) {
                $tlds = array_filter($tlds, function ($tld) use ($search) {
                    return stripos($tld['tld'], $search) !== false;
                });
            }

            if (empty($tlds)) {
                $io->warning('No TLDs found' . ($search ? " matching '{$search}'" : ''));
                return Command::SUCCESS;
            }

            if ($format === 'json') {
                $this->outputJson($io, $tlds);
            } elseif ($format === 'yaml') {
                $this->outputYaml($io, $tlds);
            } else {
                $this->outputTable($io, $tlds);
            }

            $io->success(sprintf('Found %d TLDs', count($tlds)));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error fetching TLDs: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function outputTable(SymfonyStyle $io, array $tlds): void
    {
        $io->title('Available TLDs');

        $table = new Table($io);
        $table->setHeaders(['TLD', 'Min Period', 'Max Period', 'Registration Periods', 'Price (USD)']);

        foreach (array_slice($tlds, 0, 50) as $tld) {
            $price = $this->findPrice($tld);
            $table->addRow([
                '.' . $tld['tld'],
                $tld['minRegistrationPeriod'] ?? 'N/A',
                $tld['maxRegistrationPeriod'] ?? 'N/A',
                $tld['registrationPeriods'] ? implode(', ', $tld['registrationPeriods']) : 'N/A',
                $price !== null ? number_format($price, 2) : 'N/A',
            ]);
        }

        $table->render();

        if (count($tlds) > 50) {
            $io->note(sprintf('Showing first 50 of %d TLDs. Use --search to filter.', count($tlds)));
        }
    }

    private function outputJson(SymfonyStyle $io, array $tlds): void
    {
        $io->writeln(json_encode(array_values($tlds), JSON_PRETTY_PRINT));
    }

    private function outputYaml(SymfonyStyle $io, array $tlds): void
    {
        if (!function_exists('yaml_emit')) {
            $io->warning('YAML extension not installed, falling back to JSON');
            $this->outputJson($io, $tlds);
            return;
        }

        $io->writeln(yaml_emit(array_values($tlds)));
    }

    private function findPrice(array $tld): ?float
    {
        if (isset($tld['prices']) && is_array($tld['prices'])) {
            foreach ($tld['prices'] as $price) {
                if (isset($price['register']) && is_array($price['register'])) {
                    foreach ($price['register'] as $registerPrice) {
                        if (isset($registerPrice['price'])) {
                            return (float) $registerPrice['price'];
                        }
                    }
                }
            }
        }
        return null;
    }
}