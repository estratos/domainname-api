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

    /**
     * {@inheritDoc}
     */
    public function GetResellerDetails()
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::GetResellerDetails();
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function GetCurrentBalance($currencyId = 'USD')
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::GetCurrentBalance($currencyId);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function CheckAvailability($domains, $extensions, $period, $Command)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::CheckAvailability($domains, $extensions, $period, $Command);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function GetList($extra_parameters = [])
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::GetList($extra_parameters);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function GetTldList($count = 20)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::GetTldList($count);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function GetDetails($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::GetDetails($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function ModifyNameServer($domainName, $nameServers)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::ModifyNameServer($domainName, $nameServers);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function EnableTheftProtectionLock($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::EnableTheftProtectionLock($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function DisableTheftProtectionLock($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::DisableTheftProtectionLock($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function AddChildNameServer($domainName, $nameServer, $ipAddress)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::AddChildNameServer($domainName, $nameServer, $ipAddress);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function DeleteChildNameServer($domainName, $nameServer)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::DeleteChildNameServer($domainName, $nameServer);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function ModifyChildNameServer($domainName, $nameServer, $ipAddress)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::ModifyChildNameServer($domainName, $nameServer, $ipAddress);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function GetContacts($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::GetContacts($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function SaveContacts($domainName, $contacts)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::SaveContacts($domainName, $contacts);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function Transfer($domainName, $eppCode, $period)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::Transfer($domainName, $eppCode, $period);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function CancelTransfer($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::CancelTransfer($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function ApproveTransfer($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::ApproveTransfer($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function RejectTransfer($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::RejectTransfer($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function Renew($domainName, $period)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::Renew($domainName, $period);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function RegisterWithContactInfo(
        $domainName,
        $period,
        $contacts,
        $nameServers = self::DEFAULT_NAMESERVERS,
        $eppLock = true,
        $privacyLock = false,
        $addionalAttributes = []
    ) {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::RegisterWithContactInfo(
            $domainName,
            $period,
            $contacts,
            $nameServers,
            $eppLock,
            $privacyLock,
            $addionalAttributes
        );
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function ModifyPrivacyProtectionStatus($domainName, $status, $reason = self::DEFAULT_REASON)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::ModifyPrivacyProtectionStatus($domainName, $status, $reason);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function SyncFromRegistry($domainName)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::SyncFromRegistry($domainName);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function CheckTransfer($domainName, $authcode)
    {
        $this->logCall(__FUNCTION__, func_get_args());
        $result = parent::CheckTransfer($domainName, $authcode);
        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * Métodos adicionales específicos del bundle
     */

    /**
     * Verifica si un dominio está disponible
     */
    public function isDomainAvailable(string $domain): bool
    {
        $result = $this->CheckAvailability([$domain], [substr($domain, strpos($domain, '.') + 1)], 1, 'create');
        
        if (isset($result[0]['Status'])) {
            return $result[0]['Status'] === 'Available';
        }
        
        return false;
    }

    /**
     * Obtiene información simplificada del dominio
     */
    public function getDomainInfo(string $domain): array
    {
        $details = $this->GetDetails($domain);
        
        if ($details['result'] === 'OK') {
            return [
                'domain' => $domain,
                'status' => $details['data']['Status'],
                'expires' => $details['data']['Dates']['Expiration'],
                'locked' => $details['data']['LockStatus'] === 'true',
                'privacy' => $details['data']['PrivacyProtectionStatus'] === 'true',
            ];
        }
        
        return ['error' => $details['error'] ?? 'Unknown error'];
    }

    /**
     * Registra un dominio con configuración simplificada
     */
    public function registerDomainSimple(
        string $domain,
        int $years,
        array $contactInfo,
        array $nameServers = self::DEFAULT_NAMESERVERS
    ): array {
        return $this->RegisterWithContactInfo(
            $domain,
            $years,
            $contactInfo,
            $nameServers
        );
    }

    /**
     * Logging de llamadas a la API
     */
    private function logCall(string $method, array $arguments): void
    {
        if ($this->logger) {
            $this->logger->debug('DomainName API Call', [
                'method' => $method,
                'arguments' => $this->sanitizeArguments($arguments),
            ]);
        }
    }

    /**
     * Logging de resultados
     */
    private function logResult(string $method, $result): void
    {
        if ($this->logger) {
            $logData = [
                'method' => $method,
                'success' => isset($result['result']) && $result['result'] === 'OK',
            ];

            if (isset($result['result']) && $result['result'] === 'ERROR') {
                $logData['error'] = $result['error'] ?? 'Unknown error';
            }

            $this->logger->debug('DomainName API Result', $logData);
        }
    }

    /**
     * Sanitiza argumentos para logging (remueve información sensible)
     */
    private function sanitizeArguments(array $arguments): array
    {
        $sanitized = [];
        
        foreach ($arguments as $key => $value) {
            if (is_string($value)) {
                // Oculta contraseñas y códigos de autorización
                if (in_array($key, ['password', 'authcode', 'eppCode', 'AuthCode'], true) ||
                    stripos($key, 'password') !== false ||
                    stripos($key, 'auth') !== false) {
                    $sanitized[$key] = '***HIDDEN***';
                } else {
                    $sanitized[$key] = $value;
                }
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArguments($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

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