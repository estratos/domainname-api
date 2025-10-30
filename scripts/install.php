<?php

namespace Estratos\DomainNameApi\Scripts;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class InstallScript
{
    public static function postInstall(Event $event)
    {
        $io = $event->getIO();
        
        $io->write('<info>Configuring DomainName API Bundle...</info>');
        
        // Verificar variables de entorno
        self::checkEnvironment($io);
        
        $io->write('<info>DomainName API Bundle installed successfully!</info>');
    }
    
    private static function checkEnvironment($io)
    {
        $envFile = getcwd() . '/.env';
        $envLocalFile = getcwd() . '/.env.local';
        
        $targetFile = file_exists($envLocalFile) ? $envLocalFile : $envFile;
        
        if (!file_exists($targetFile)) {
            $io->warning('No .env file found. Please create one and configure your DomainName API credentials.');
            return;
        }
        
        $envContent = file_get_contents($targetFile);
        $requiredVars = [
            'DOMAINNAME_API_USERNAME',
            'DOMAINNAME_API_PASSWORD'
        ];
        
        $missingVars = [];
        foreach ($requiredVars as $var) {
            if (strpos($envContent, $var) === false) {
                $missingVars[] = $var;
            }
        }
        
        if (!empty($missingVars)) {
            $io->warning('Please add the following environment variables to your ' . basename($targetFile) . ' file:');
            foreach ($missingVars as $var) {
                $io->write("  - $var");
            }
            $io->write('');
            $io->write('Example configuration:');
            $io->write('DOMAINNAME_API_USERNAME=your_username_here');
            $io->write('DOMAINNAME_API_PASSWORD=your_password_here');
            $io->write('DOMAINNAME_API_TEST_MODE=false');
        } else {
            $io->success('Environment variables are properly configured!');
        }
    }
}