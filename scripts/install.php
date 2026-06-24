<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Scripts;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class InstallScript
{
    public static function postInstall(Event $event): void
    {
        $io = $event->getIO();

        $io->write('');
        $io->write('<info>=============================================</info>');
        $io->write('<info>  DomainNameAPI Bundle Installation</info>');
        $io->write('<info>=============================================</info>');
        $io->write('');

        // Verificar variables de entorno
        self::checkEnvironment($io);

        // Mostrar información de configuración
        self::showConfigurationHelp($io);

        $io->write('');
        $io->write('<info>✅ DomainNameAPI Bundle installed successfully!</info>');
        $io->write('');
    }

    private static function checkEnvironment($io): void
    {
        $envFile = getcwd() . '/.env';
        $envLocalFile = getcwd() . '/.env.local';

        $targetFile = file_exists($envLocalFile) ? $envLocalFile : $envFile;

        if (!file_exists($targetFile)) {
            $io->warning('No .env file found. Please create one and configure your credentials.');
            return;
        }

        $envContent = file_get_contents($targetFile);

        // Variables requeridas para REST
        $restVars = [
            'DOMAINNAME_API_KEY',
            'DOMAINNAME_RESELLER_ID'
        ];

        // Variables requeridas para SOAP (legacy)
        $soapVars = [
            'DOMAINNAME_API_USERNAME',
            'DOMAINNAME_API_PASSWORD'
        ];

        $missingRestVars = [];
        foreach ($restVars as $var) {
            if (strpos($envContent, $var) === false) {
                $missingRestVars[] = $var;
            }
        }

        $missingSoapVars = [];
        foreach ($soapVars as $var) {
            if (strpos($envContent, $var) === false) {
                $missingSoapVars[] = $var;
            }
        }

        $io->write('<comment>Environment Variables Status:</comment>');

        // REST variables
        if (empty($missingRestVars)) {
            $io->write('  ✅ REST API variables: All set');
        } else {
            $io->write('  ⚠️  REST API variables missing:');
            foreach ($missingRestVars as $var) {
                $io->write("     - $var");
            }
        }

        // SOAP variables (legacy)
        if (empty($missingSoapVars)) {
            $io->write('  ✅ SOAP API variables: All set');
        } else {
            $io->write('  ⚠️  SOAP API variables missing (optional):');
            foreach ($missingSoapVars as $var) {
                $io->write("     - $var");
            }
        }

        if (!empty($missingRestVars) && !empty($missingSoapVars)) {
            $io->write('');
            $io->warning('Please add at least REST API variables to your .env file.');
            $io->write('');
            $io->write('Example configuration:');
            $io->write('  DOMAINNAME_API_PROVIDER=rest');
            $io->write('  DOMAINNAME_API_KEY=your_api_key_here');
            $io->write('  DOMAINNAME_RESELLER_ID=your_reseller_id_here');
            $io->write('  DOMAINNAME_API_ENDPOINT=https://api.domainresellerapi.com');
        }
    }

    private static function showConfigurationHelp($io): void
    {
        $io->write('');
        $io->write('<comment>Quick Start:</comment>');
        $io->write('  1. Configure your credentials in .env');
        $io->write('  2. Test connection:');
        $io->write('     <info>php bin/console domain:test-rest</info>');
        $io->write('  3. Check a domain:');
        $io->write('     <info>php bin/console domain:check example.com</info>');
        $io->write('  4. View your balance:');
        $io->write('     <info>php bin/console domain:balance</info>');
        $io->write('');
        $io->write('<comment>Available Commands:</comment>');
        $io->write('  <info>domain:check</info>        - Check domain availability');
        $io->write('  <info>domain:balance</info>      - Check account balance');
        $io->write('  <info>domain:list</info>         - List domains');
        $io->write('  <info>domain:tlds</info>         - List available TLDs');
        $io->write('  <info>domain:register</info>     - Register a new domain');
        $io->write('  <info>domain:test-rest</info>    - Test REST API connection');
        $io->write('');
    }
}