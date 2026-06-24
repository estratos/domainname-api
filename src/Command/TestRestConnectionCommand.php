<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
        private RestClient $restClient
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Testing REST API Connection');
        
        try {
            // Intentar obtener el balance (requiere autenticación)
            $balance = $this->restClient->get('/api/v1/deposit/accounts/me');
            
            $io->success('✅ REST API connection successful!');
            $io->writeln(sprintf('Balance: $%s', $balance['usdBalance'] ?? 'N/A'));
            
            // Probar listado de TLDs
            $tlds = $this->restClient->get('/api/v1/products/tlds', [
                'MaxResultCount' => 10,
            ]);
            
            $io->writeln('Available TLDs:');
            foreach ($tlds['items'] ?? [] as $tld) {
                $io->writeln(sprintf('  - .%s', $tld['name'] ?? 'unknown'));
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ REST API connection failed: ' . $e->getMessage());
            $io->writeln('Please check your credentials:');
            $io->writeln('  - API Key: ' . (getenv('DOMAINNAME_API_KEY') ? '✓ Set' : '✗ Not set'));
            $io->writeln('  - Reseller ID: ' . (getenv('DOMAINNAME_RESELLER_ID') ? '✓ Set' : '✗ Not set'));
            $io->writeln('  - Endpoint: ' . (getenv('DOMAINNAME_API_ENDPOINT') ?: 'https://api.domainresellerapi.com'));
            
            return Command::FAILURE;
        }
    }
}