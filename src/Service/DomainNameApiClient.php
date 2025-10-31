<?php

namespace Estratos\DomainNameApi\Service;

use DomainNameApi\DomainNameAPI_PHPLibrary;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

/**
 * Cliente principal para DomainName API
 * Hereda todos los métodos de DomainNameAPI_PHPLibrary y añade funcionalidades Symfony
 */
class DomainNameApiClient extends DomainNameAPI_PHPLibrary implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

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
        $tld = substr($domain, strpos($domain, '.') + 1);
        $result = $this->CheckAvailability([$domain], [$tld], 1, 'create');
        
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
            if ($this->logger) {
                $this->logger->error('DomainName API Connection Test Failed', [
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Obtiene el logger si está disponible
     */
    private function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
/**
     * Get TLD pricing information with formatted structure
     *
     * @param string|null $tld Specific TLD to get pricing for (optional)
     * @param string $currency Preferred currency (USD, TRY, etc.)
     * @param int $period Registration period in years
     * @param array $operations Types of operations to get pricing for (create, renew, transfer, etc.)
     * @return array Formatted TLD pricing information
     */
    public function GetTldPricing(
        ?string $tld = null,
        string $currency = 'USD',
        int $period = 1,
        array $operations = ['create', 'renew', 'transfer']
    ): array {
        $this->logCall(__FUNCTION__, func_get_args());
        
        // Obtener lista completa de TLDs
        $tldListResult = $this->GetTldList(1000); // Número alto para obtener todos los TLDs
        
        if ($tldListResult['result'] !== 'OK') {
            $this->logResult(__FUNCTION__, $tldListResult);
            return $tldListResult;
        }

        $result = $this->processTldPricing(
            $tldListResult['data'] ?? [],
            $tld,
            $currency,
            $period,
            $operations
        );

        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * Process TLD list and extract pricing information
     */
    private function processTldPricing(
        array $tlds,
        ?string $filterTld,
        string $currency,
        int $period,
        array $operations
    ): array {
        $pricingData = [];
        $supportedCurrencies = ['USD', 'TRY'];
        $selectedCurrency = in_array(strtoupper($currency), $supportedCurrencies) ? strtoupper($currency) : 'USD';

        foreach ($tlds as $tldInfo) {
            $currentTld = $tldInfo['tld'] ?? '';
            
            // Filtrar por TLD específico si se proporciona
            if ($filterTld && $currentTld !== $filterTld) {
                continue;
            }

            $tldPricing = [
                'tld' => $currentTld,
                'status' => $tldInfo['status'] ?? '',
                'limits' => [
                    'min_chars' => $tldInfo['minchar'] ?? 0,
                    'max_chars' => $tldInfo['maxchar'] ?? 0,
                    'min_period' => $tldInfo['minperiod'] ?? 1,
                    'max_period' => $tldInfo['maxperiod'] ?? 10,
                ],
                'pricing' => [],
                'currency' => $selectedCurrency,
            ];

            // Procesar precios para las operaciones solicitadas
            foreach ($operations as $operation) {
                $operation = strtolower($operation);
                $tldPricing['pricing'][$operation] = $this->extractOperationPricing(
                    $tldInfo,
                    $operation,
                    $selectedCurrency,
                    $period
                );
            }

            $pricingData[] = $tldPricing;
        }

        // Si se filtró por un TLD específico y no se encontró
        if ($filterTld && empty($pricingData)) {
            return [
                'result' => 'ERROR',
                'error' => $this->setError('TLD_NOT_FOUND', "TLD '{$filterTld}' not found or not supported")
            ];
        }

        return [
            'result' => 'OK',
            'data' => $pricingData,
            'metadata' => [
                'total_tlds' => count($pricingData),
                'currency' => $selectedCurrency,
                'period' => $period,
                'operations' => $operations,
                'filtered' => $filterTld !== null,
            ]
        ];
    }

    /**
     * Extract pricing for a specific operation
     */
    private function extractOperationPricing(
        array $tldInfo,
        string $operation,
        string $currency,
        int $period
    ): array {
        $pricing = [
            'available' => false,
            'price' => null,
            'currency' => $currency,
            'period' => $period,
        ];

        $operationMap = [
            'create' => 'registration',
            'register' => 'registration',
            'renew' => 'renewal',
            'transfer' => 'transfer',
            'restore' => 'restore',
        ];

        $apiOperation = $operationMap[$operation] ?? $operation;

        // Buscar en la estructura de precios existente
        if (isset($tldInfo['pricing'][$apiOperation][$period])) {
            $pricing['available'] = true;
            $pricing['price'] = $tldInfo['pricing'][$apiOperation][$period];
            $pricing['currency'] = $tldInfo['currencies'][$apiOperation] ?? $currency;
        } else {
            // Intentar encontrar el precio para el período más cercano
            $availablePeriods = array_keys($tldInfo['pricing'][$apiOperation] ?? []);
            if (!empty($availablePeriods)) {
                $closestPeriod = $this->findClosestPeriod($availablePeriods, $period);
                $pricing['available'] = true;
                $pricing['price'] = $tldInfo['pricing'][$apiOperation][$closestPeriod];
                $pricing['currency'] = $tldInfo['currencies'][$apiOperation] ?? $currency;
                $pricing['period'] = $closestPeriod;
                $pricing['note'] = "Using period {$closestPeriod} (requested: {$period})";
            }
        }

        return $pricing;
    }

    /**
     * Find closest available period
     */
    private function findClosestPeriod(array $availablePeriods, int $targetPeriod): int
    {
        $closest = null;
        $minDifference = PHP_INT_MAX;

        foreach ($availablePeriods as $period) {
            $difference = abs($period - $targetPeriod);
            if ($difference < $minDifference) {
                $minDifference = $difference;
                $closest = $period;
            }
        }

        return $closest ?? $availablePeriods[0];
    }

    /**
     * Get pricing for multiple TLDs in batch
     *
     * @param array $tlds Array of TLDs to get pricing for
     * @param string $currency Preferred currency
     * @param int $period Registration period
     * @param array $operations Types of operations
     * @return array Batch pricing information
     */
    public function GetTldPricingBatch(
        array $tlds,
        string $currency = 'USD',
        int $period = 1,
        array $operations = ['create', 'renew', 'transfer']
    ): array {
        $this->logCall(__FUNCTION__, func_get_args());

        $batchResult = [];
        $errors = [];

        foreach ($tlds as $tld) {
            $tld = ltrim($tld, '.'); // Asegurar formato correcto
            $pricing = $this->GetTldPricing($tld, $currency, $period, $operations);
            
            if ($pricing['result'] === 'OK') {
                $batchResult[$tld] = $pricing['data'][0] ?? null;
            } else {
                $errors[$tld] = $pricing['error'] ?? 'Unknown error';
                $batchResult[$tld] = null;
            }
        }

        $result = [
            'result' => empty($errors) ? 'OK' : 'PARTIAL',
            'data' => $batchResult,
            'metadata' => [
                'total_requested' => count($tlds),
                'total_resolved' => count($batchResult) - count($errors),
                'errors' => $errors,
                'currency' => $currency,
                'period' => $period,
            ]
        ];

        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * Get available TLDs with basic pricing information
     *
     * @param string $currency Preferred currency
     * @return array Simplified TLD list with create pricing
     */
    public function GetAvailableTlds(string $currency = 'USD'): array
    {
        $this->logCall(__FUNCTION__, func_get_args());

        $pricing = $this->GetTldPricing(null, $currency, 1, ['create']);

        if ($pricing['result'] !== 'OK') {
            $this->logResult(__FUNCTION__, $pricing);
            return $pricing;
        }

        // Filtrar solo TLDs disponibles
        $availableTlds = array_filter($pricing['data'], function($tld) {
            return $tld['status'] === 'Active' && 
                   $tld['pricing']['create']['available'] === true;
        });

        // Formatear respuesta simplificada
        $simplifiedTlds = array_map(function($tld) {
            return [
                'tld' => $tld['tld'],
                'price' => $tld['pricing']['create']['price'],
                'currency' => $tld['pricing']['create']['currency'],
                'min_period' => $tld['limits']['min_period'],
                'max_period' => $tld['limits']['max_period'],
                'min_chars' => $tld['limits']['min_chars'],
                'max_chars' => $tld['limits']['max_chars'],
            ];
        }, $availableTlds);

        $result = [
            'result' => 'OK',
            'data' => array_values($simplifiedTlds),
            'metadata' => [
                'total_available' => count($simplifiedTlds),
                'currency' => $currency,
            ]
        ];

        $this->logResult(__FUNCTION__, $result);
        return $result;
    }

    /**
     * Compare pricing between different TLDs
     *
     * @param array $tlds TLDs to compare
     * @param string $operation Operation type
     * @param string $currency Currency
     * @param int $period Period
     * @return array Comparison results
     */
    public function CompareTldPricing(
        array $tlds,
        string $operation = 'create',
        string $currency = 'USD',
        int $period = 1
    ): array {
        $this->logCall(__FUNCTION__, func_get_args());

        $comparison = [];
        $operation = strtolower($operation);

        foreach ($tlds as $tld) {
            $pricing = $this->GetTldPricing($tld, $currency, $period, [$operation]);
            
            if ($pricing['result'] === 'OK' && !empty($pricing['data'])) {
                $tldData = $pricing['data'][0];
                $comparison[$tld] = [
                    'available' => $tldData['pricing'][$operation]['available'],
                    'price' => $tldData['pricing'][$operation]['price'],
                    'currency' => $tldData['pricing'][$operation]['currency'],
                    'status' => $tldData['status'],
                    'limits' => $tldData['limits'],
                ];
            } else {
                $comparison[$tld] = [
                    'available' => false,
                    'error' => $pricing['error'] ?? 'TLD not found',
                ];
            }
        }

        // Ordenar por precio
        uasort($comparison, function($a, $b) {
            if (!$a['available'] && !$b['available']) return 0;
            if (!$a['available']) return 1;
            if (!$b['available']) return -1;
            return $a['price'] <=> $b['price'];
        });

        $result = [
            'result' => 'OK',
            'data' => $comparison,
            'metadata' => [
                'operation' => $operation,
                'currency' => $currency,
                'period' => $period,
                'cheapest' => array_key_first($comparison),
            ]
        ];

        $this->logResult(__FUNCTION__, $result);
        return $result;
    }


}
