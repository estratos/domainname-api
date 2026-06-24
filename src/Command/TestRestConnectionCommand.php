<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:test-rest',
    description: 'Test REST API connection'
)]
class TestRestConnectionCommand extends Command
{
    public function __construct(
        private readonly RestClient $restClient
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔌 DomainResellerAPI REST Connection Test');

        $io->section('Testing Authentication');

        try {
            // Probar balance (requiere autenticación)
            $startTime = microtime(true);
            $balance = $this->restClient->get('/api/v1/deposit/accounts/me');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $io->success('✅ REST API connection successful!');
            $io->note(sprintf('Response time: %sms', $responseTime));

            // Mostrar información del balance
            $table = new Table($io);
            $table->setHeaders(['Property', 'Value']);
            $table->addRows([
                ['Status', '✅ Connected'],
                ['Balance', sprintf('$%s', number_format($balance['usdBalance'] ?? 0, 2))],
                ['Reseller ID', $balance['resellerId'] ?? 'N/A'],
                ['Reseller Name', $balance['resellerName'] ?? 'N/A'],
                ['Endpoint', $this->getEndpoint()],
            ]);
            $table->render();

            // Probar TLDs
            $io->section('Testing TLDs Endpoint');
            $tlds = $this->restClient->get('/api/v1/products/tlds', [
                'MaxResultCount' => 5,
            ]);

            $tldList = $tlds['items'] ?? [];
            if (!empty($tldList)) {
                $io->success(sprintf('✅ TLDs endpoint working! Found %d TLDs (showing first 5)', $tlds['totalCount'] ?? count($tldList)));
                foreach (array_slice($tldList, 0, 5) as $tld) {
                    $io->writeln(sprintf('  - .%s', $tld['name'] ?? 'unknown'));
                }
            } else {
                $io->warning('⚠️  TLDs endpoint returned no results');
            }

            // Probar búsqueda de dominio
            $io->section('Testing Domain Search');
            $search = $this->restClient->post('/api/v1/domains/search', [
                'domainName' => 'testsearch' . rand(1000, 9999) . '.com',
            ]);

            $io->success('✅ Domain search working');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ REST API connection failed: ' . $e->getMessage());

            $io->section('Troubleshooting');
            $io->writeln('Please check your configuration:');

            $table = new Table($io);
            $table->setHeaders(['Variable', 'Status']);
            $table->addRows([
                ['DOMAINNAME_API_KEY', $this->getEnvStatus('DOMAINNAME_API_KEY')],
                ['DOMAINNAME_RESELLER_ID', $this->getEnvStatus('DOMAINNAME_RESELLER_ID')],
                ['DOMAINNAME_API_ENDPOINT', $this->getEndpoint()],
            ]);
            $table->render();

            $io->writeln('');
            $io->writeln('Try running:');
            $io->writeln('  php bin/console domain:test-rest');

            return Command::FAILURE;
        }
    }

    private function getEndpoint(): string
    {
        return getenv('DOMAINNAME_API_ENDPOINT') ?: 'https://api.domainresellerapi.com';
    }

    private function getEnvStatus(string $name): string
    {
        $value = getenv($name);
        if (empty($value)) {
            return '❌ Not set';
        }
        if (strlen($value) < 10) {
            return '⚠️  Too short';
        }
        return sprintf('✅ Set (%s...)', substr($value, 0, 10));
    }
}