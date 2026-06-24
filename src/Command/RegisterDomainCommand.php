<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Command;

use Estratos\DomainNameApi\Application\UseCase\RegisterDomainUseCase;
use Estratos\DomainNameApi\Domain\DTO\ContactDto;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'domain:register',
    description: 'Register a new domain'
)]
class RegisterDomainCommand extends Command
{
    public function __construct(
        private readonly RegisterDomainUseCase $registerDomainUseCase
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name to register (e.g., example.com)')
            ->addOption('period', 'p', InputOption::VALUE_OPTIONAL, 'Registration period in years (1, 2, 3, 5, 10)', '1')
            ->addOption('nameservers', 'n', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Nameservers (e.g., ns1.domainnameapi.com)')
            ->addOption('privacy', null, InputOption::VALUE_NONE, 'Enable privacy protection')
            ->addOption('autorenew', null, InputOption::VALUE_NONE, 'Enable auto-renew')
            ->addOption('skip-interactive', null, InputOption::VALUE_NONE, 'Skip interactive prompts (use defaults or empty)')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (table, json, yaml)', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $domain = $input->getArgument('domain');
        $period = (int) $input->getOption('period');
        $nameservers = $input->getOption('nameservers');
        $privacy = $input->getOption('privacy');
        $autoRenew = $input->getOption('autorenew');
        $skipInteractive = $input->getOption('skip-interactive');
        $format = $input->getOption('format');

        try {
            // Recolectar datos de contacto
            $contacts = $this->collectContacts($io, $skipInteractive);

            // Construir request
            $request = new RegisterDomainRequest(
                domain: $domain,
                period: $period,
                registrant: $contacts['registrant'],
                admin: $contacts['admin'],
                technical: $contacts['technical'],
                billing: $contacts['billing'],
                nameservers: $nameservers ?: ['ns1.domainnameapi.com', 'ns2.domainnameapi.com'],
                privacyProtection: $privacy,
                autoRenew: $autoRenew ? 1 : 0,
            );

            // Confirmar registro
            if (!$skipInteractive && !$io->confirm(sprintf('Register domain "%s" for %d year(s)?', $domain, $period), true)) {
                $io->warning('Registration cancelled');
                return Command::SUCCESS;
            }

            $io->info('Registering domain...');
            $result = $this->registerDomainUseCase->execute($request);

            if ($format === 'json') {
                $this->outputJson($io, $result);
            } elseif ($format === 'yaml') {
                $this->outputYaml($io, $result);
            } else {
                $this->outputTable($io, $result);
            }

            if ($result->isSuccess()) {
                $io->success(sprintf('✅ Domain "%s" registered successfully!', $domain));
                if ($result->orderId) {
                    $io->note(sprintf('Order ID: %s', $result->orderId));
                }
                if ($result->expirationDate) {
                    $io->note(sprintf('Expires: %s', $result->expirationDate->format('Y-m-d')));
                }
            } else {
                $io->error('❌ Registration failed: ' . ($result->getErrorMessage() ?? 'Unknown error'));
            }

            return $result->isSuccess() ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $io->error(sprintf('Error registering domain: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function collectContacts(SymfonyStyle $io, bool $skipInteractive): array
    {
        if ($skipInteractive) {
            // Datos ficticios para uso en modo no interactivo
            $defaultContact = new ContactDto(
                firstName: 'John',
                lastName: 'Doe',
                company: 'Example Inc.',
                email: 'admin@example.com',
                addressLine1: '123 Main Street',
                addressLine2: '',
                city: 'New York',
                state: 'NY',
                country: 'US',
                zipCode: '10001',
                phone: '+1234567890',
                phoneCountryCode: '1'
            );

            return [
                'registrant' => clone $defaultContact,
                'admin' => clone $defaultContact,
                'technical' => clone $defaultContact,
                'billing' => clone $defaultContact,
            ];
        }

        $io->section('Contact Information');

        // Recolectar datos de contacto
        $firstName = $this->askQuestion($io, 'First Name', 'John');
        $lastName = $this->askQuestion($io, 'Last Name', 'Doe');
        $company = $this->askQuestion($io, 'Company (optional)', null);
        $email = $this->askQuestion($io, 'Email', 'admin@example.com');
        $addressLine1 = $this->askQuestion($io, 'Address Line 1', '123 Main Street');
        $addressLine2 = $this->askQuestion($io, 'Address Line 2 (optional)', null);
        $city = $this->askQuestion($io, 'City', 'New York');
        $state = $this->askQuestion($io, 'State/Province', 'NY');
        $country = $this->askQuestion($io, 'Country (2-letter code)', 'US');
        $zipCode = $this->askQuestion($io, 'Postal Code', '10001');
        $phone = $this->askQuestion($io, 'Phone (with country code)', '+1234567890');

        $contact = new ContactDto(
            firstName: $firstName,
            lastName: $lastName,
            company: $company ?? '',
            email: $email,
            addressLine1: $addressLine1,
            addressLine2: $addressLine2 ?? '',
            city: $city,
            state: $state,
            country: strtoupper($country),
            zipCode: $zipCode,
            phone: $phone,
            phoneCountryCode: $this->extractCountryCode($phone)
        );

        return [
            'registrant' => clone $contact,
            'admin' => clone $contact,
            'technical' => clone $contact,
            'billing' => clone $contact,
        ];
    }

    private function askQuestion(SymfonyStyle $io, string $question, ?string $default = null): string
    {
        $questionObj = new Question($question, $default);
        return $io->askQuestion($questionObj);
    }

    private function extractCountryCode(string $phone): string
    {
        if (preg_match('/^\+(\d+)/', $phone, $matches)) {
            return $matches[1];
        }
        return '1';
    }

    private function outputTable(SymfonyStyle $io, $result): void
    {
        $io->title('Registration Result');

        $table = new Table($io);
        $table->setHeaders(['Property', 'Value']);
        $table->addRows([
            ['Domain', $result->domain],
            ['Success', $result->success ? '✅ Yes' : '❌ No'],
            ['Order ID', $result->orderId ?? 'N/A'],
            ['Transaction ID', $result->transactionId ?? 'N/A'],
            ['Message', $result->message ?? 'N/A'],
            ['Status', $result->status ?? 'N/A'],
            ['Expiration Date', $result->expirationDate?->format('Y-m-d') ?? 'N/A'],
        ]);
        $table->render();
    }

    private function outputJson(SymfonyStyle $io, $result): void
    {
        $data = [
            'domain' => $result->domain,
            'success' => $result->success,
            'orderId' => $result->orderId,
            'transactionId' => $result->transactionId,
            'message' => $result->message,
            'status' => $result->status,
            'expirationDate' => $result->expirationDate?->format('Y-m-d'),
            'errors' => $result->errors,
        ];
        $io->writeln(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function outputYaml(SymfonyStyle $io, $result): void
    {
        $data = [
            'domain' => $result->domain,
            'success' => $result->success,
            'orderId' => $result->orderId,
            'transactionId' => $result->transactionId,
            'message' => $result->message,
            'status' => $result->status,
            'expirationDate' => $result->expirationDate?->format('Y-m-d'),
            'errors' => $result->errors,
        ];

        if (!function_exists('yaml_emit')) {
            $io->warning('YAML extension not installed, falling back to JSON');
            $this->outputJson($io, $result);
            return;
        }

        $io->writeln(yaml_emit($data));
    }
}