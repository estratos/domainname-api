<?php

namespace Symfony\Flex\Recipe;

use Composer\Script\Event;

class DomainNameApiRecipeInstaller
{
    public static function install(Event $event): void
    {
        $io = $event->getIO();
        
        $io->write('<info>Configuring DomainName API Bundle...</info>');
        
        // Verificar que las variables de entorno estén configuradas
        self::checkEnvVars($io);
        
        $io->write('<info>DomainName API Bundle configured successfully!</info>');
        $io->write('');
        $io->write('Next steps:');
        $io->write('1. Update your .env file with your DomainName API credentials');
        $io->write('2. Run: php bin/console domain:balance to test the connection');
        $io->write('3. Check available commands: php bin/console | grep domain');
    }

    private static function checkEnvVars($io): void
    {
        $requiredVars = [
            'DOMAINNAME_API_USERNAME',
            'DOMAINNAME_API_PASSWORD'
        ];

        foreach ($requiredVars as $var) {
            if (empty($_ENV[$var] ?? null)) {
                $io->warning(sprintf(
                    'Please set the %s environment variable in your .env file',
                    $var
                ));
            }
        }
    }
}
