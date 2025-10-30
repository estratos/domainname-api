<?php

namespace Estratos\DomainNameApi\Service;

use DomainNameApi\DomainNameAPI_PHPLibrary;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Cliente principal para DomainName API
 * Hereda todos los métodos de DomainNameAPI_PHPLibrary y añade funcionalidades Symfony
 */
class DomainNameApiClient extends DomainNameAPI_PHPLibrary implements ServiceSubscriberInterface
{
    private ?LoggerInterface $logger;
    private array $config;

    public function __construct(
        string $username,
        string $password,
        bool $testMode = false,
        ?LoggerInterface $logger = null,
        array $config = []
    ) {
        // Llamar al constructor padre con los parámetros requeridos
        parent::__construct($username, $password, $testMode);
        
        $this->logger = $logger;
        $this->config = array_merge([
            'timeout' => self::DEFAULT_TIMEOUT,
            'cache_ttl' => self::DEFAULT_CACHE_TTL,
            'default_nameservers' => self::DEFAULT_NAMESERVERS,
            'default_reason' => self::DEFAULT_REASON,
        ], $config);
    }

    public static function getSubscribedServices(): array
    {
        return [
            LoggerInterface::class,
        ];
    }

    // ... (mantener todos los métodos heredados igual)

    /**
     * Obtiene la configuración actual
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Verifica la conectividad con la API
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->GetResellerDetails();
            return isset($result['result']) && $result['result'] === 'OK';
        } catch (\Exception $e) {
            return false;
        }
    }
}