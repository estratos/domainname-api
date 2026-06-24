<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Application\UseCase\ListDomainsUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:list',
    description: 'List domains in your account'
)]
class ListDomainsCommand extends Command
{
    public function __construct(
        private readonly ListDomainsUseCase $listDomainsUseCase
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'page',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Page number',
                '1'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Items per page (max 200)',
                '50'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Output format (table, json, yaml)',
                'table'
            )
            ->addOption(
                'status',
                's',
                InputOption::VALUE_OPTIONAL,
                'Filter by status (active, expired, pending, all)',
                'all'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $page = (int) $input->getOption('page');
        $limit = (int) $input->getOption('limit');
        $format = $input->getOption('format');
        $statusFilter = $input->getOption('status');

        try {
            $io->info(sprintf('Fetching domains (page %d, limit %d)...', $page, $limit));
            $result = $this->listDomainsUseCase->execute($page, $limit);

            if ($result->total === 0) {
                $io->warning('No domains found');
                return Command::SUCCESS;
            }

            // Filtrar por estado si se especifica
            $domains = $result->getDomains();
            if ($statusFilter !== 'all') {
                $domains = array_filter($domains, function ($domain) use ($statusFilter) {
                    if ($statusFilter === 'active') {
                        return $domain->isActive();
                    }
                    if ($statusFilter === 'expired') {
                        return $domain->isExpired();
                    }
                    if ($statusFilter === 'pending') {
                        return $domain->status === 'pending';
                    }
                    return true;
                });
            }

            if ($format === 'json') {
                $this->outputJson($io, $domains, $result);
            } elseif ($format === 'yaml') {
                $this->outputYaml($io, $domains, $result);
            } else {
                $this->outputTable($io, $domains, $result);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error listing domains: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function outputTable(SymfonyStyle $io, array $domains, $result): void
    {
        $io->title(sprintf('Domains (Page %d of %d)', $result->page, $result->getTotalPages()));

        if (empty($domains)) {
            $io->warning('No domains match the specified filters');
            return;
        }

        $table = new Table($io);
        $table->setHeaders(['Domain', 'Status', 'Expires', 'Days Left', 'Auto Renew', 'Privacy']);

        foreach ($domains as $domain) {
            $daysLeft = $domain->getDaysUntilExpiration();
            $daysLeftDisplay = $daysLeft !== null ? $this->colorizeDaysLeft($daysLeft) : 'N/A';

            $table->addRow([
                $domain->domain,
                $this->colorizeStatus($domain->status),
                $domain->expirationDate ? $domain->expirationDate->format('Y-m-d') : 'N/A',
                $daysLeftDisplay,
                $domain->autoRenew ? '✅' : '❌',
                $domain->privacyProtection ? '✅' : '❌',
            ]);
        }

        $table->render();

        $io->newLine();
        $io->note(sprintf(
            'Showing %d of %d total domains',
            count($domains),
            $result->total
        ));

        // Navegación
        if ($result->hasPreviousPage() || $result->hasNextPage()) {
            $nav = [];
            if ($result->hasPreviousPage()) {
                $nav[] = '--page=' . ($result->page - 1);
            }
            if ($result->hasNextPage()) {
                $nav[] = '--page=' . ($result->page + 1);
            }
            $io->note('To navigate: php bin/console domain:list ' . implode(' ', $nav));
        }
    }

    private function outputJson(SymfonyStyle $io, array $domains, $result): void
    {
        $data = [
            'total' => $result->total,
            'page' => $result->page,
            'limit' => $result->limit,
            'totalPages' => $result->getTotalPages(),
            'domains' => array_map(function ($domain) {
                return [
                    'domain' => $domain->domain,
                    'status' => $domain->status,
                    'registrationDate' => $domain->registrationDate?->format('Y-m-d H:i:s'),
                    'expirationDate' => $domain->expirationDate?->format('Y-m-d H:i:s'),
                    'autoRenew' => $domain->autoRenew,
                    'privacyProtection' => $domain->privacyProtection,
                    'locked' => $domain->locked,
                    'remainingDays' => $domain->remainingDays,
                    'nameservers' => $domain->nameservers,
                ];
            }, $domains),
        ];
        $io->writeln(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function outputYaml(SymfonyStyle $io, array $domains, $result): void
    {
        $data = [
            'total' => $result->total,
            'page' => $result->page,
            'limit' => $result->limit,
            'totalPages' => $result->getTotalPages(),
            'domains' => array_map(function ($domain) {
                return [
                    'domain' => $domain->domain,
                    'status' => $domain->status,
                    'registrationDate' => $domain->registrationDate?->format('Y-m-d H:i:s'),
                    'expirationDate' => $domain->expirationDate?->format('Y-m-d H:i:s'),
                    'autoRenew' => $domain->autoRenew,
                    'privacyProtection' => $domain->privacyProtection,
                    'locked' => $domain->locked,
                    'remainingDays' => $domain->remainingDays,
                    'nameservers' => $domain->nameservers,
                ];
            }, $domains),
        ];
        
        if (!function_exists('yaml_emit')) {
            $io->warning('YAML extension not installed, falling back to JSON');
            $this->outputJson($io, $domains, $result);
            return;
        }

        $io->writeln(yaml_emit($data));
    }

    private function colorizeStatus(string $status): string
    {
        $colors = [
            'active' => '<fg=green>Active</>',
            'pending' => '<fg=yellow>Pending</>',
            'suspended' => '<fg=red>Suspended</>',
            'expired' => '<fg=red>Expired</>',
            'deleted' => '<fg=gray>Deleted</>',
            'transfer_pending' => '<fg=cyan>Transfer Pending</>',
            'pending_verification' => '<fg=yellow>Pending Verification</>',
        ];

        return $colors[$status] ?? $status;
    }

    private function colorizeDaysLeft(int $days): string
    {
        if ($days < 0) {
            return sprintf('<fg=red>%d days overdue</>', abs($days));
        }
        if ($days < 30) {
            return sprintf('<fg=yellow>%d days</>', $days);
        }
        if ($days < 90) {
            return sprintf('<fg=cyan>%d days</>', $days);
        }
        return sprintf('<fg=green>%d days</>', $days);
    }
}