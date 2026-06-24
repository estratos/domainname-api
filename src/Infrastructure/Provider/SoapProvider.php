<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Infrastructure\Provider;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\BalanceResult;
use Estratos\DomainNameApi\Domain\DTO\DomainCheckResult;
use Estratos\DomainNameApi\Domain\DTO\DomainCollection;
use Estratos\DomainNameApi\Domain\DTO\DomainDto;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Estratos\DomainNameApi\Domain\DTO\RegisterResult;
use Estratos\DomainNameApi\Domain\DTO\TldCollection;
use Estratos\DomainNameApi\Domain\Exception\DomainException;
use Estratos\DomainNameApi\Service\DomainNameApiClient;

/**
 * Adaptador SOAP para compatibilidad con la librería legacy.
 */
class SoapProvider implements DomainProviderInterface
{
    private DomainNameApiClient $client;

    public function __construct(DomainNameApiClient $client)
    {
        $this->client = $client;
    }

    public function check(string $domain): DomainCheckResult
    {
        try {
            $result = $this->client->CheckDomain($domain);

            if (isset($result['error'])) {
                throw new DomainException($result['error']);
            }

            return new DomainCheckResult(
                domain: $domain,
                available: isset($result['available']) && $result['available'] === true,
                price: $result['price'] ?? null,
                currency: $result['currency'] ?? 'USD',
                status: $result['status'] ?? null,
                reason: $result['reason'] ?? null,
                registrationPeriod: $result['registrationPeriod'] ?? null,
            );
        } catch (\Exception $e) {
            throw new DomainException('Error checking domain: ' . $e->getMessage(), 0, $e);
        }
    }

    public function register(RegisterDomainRequest $request): RegisterResult
    {
        try {
            $params = [
                'domain' => $request->domain,
                'period' => $request->period,
                'registrant' => $this->contactToArray($request->registrant),
                'admin' => $this->contactToArray($request->admin),
                'technical' => $this->contactToArray($request->technical),
                'billing' => $this->contactToArray($request->billing),
                'nameservers' => $request->nameservers,
                'privacyProtection' => $request->privacyProtection,
            ];

            if ($request->authCode !== null) {
                $params['authCode'] = $request->authCode;
            }

            if ($request->autoRenew !== null) {
                $params['autoRenew'] = $request->autoRenew;
            }

            $result = $this->client->RegisterDomain($params);

            if (isset($result['error'])) {
                throw new DomainException($result['error']);
            }

            return new RegisterResult(
                domain: $request->domain,
                success: true,
                orderId: $result['orderId'] ?? null,
                transactionId: $result['transactionId'] ?? null,
                message: $result['message'] ?? 'Domain registered successfully',
            );
        } catch (\Exception $e) {
            throw new DomainException('Error registering domain: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getBalance(): BalanceResult
    {
        try {
            $result = $this->client->GetCurrentBalance();

            if (isset($result['error'])) {
                throw new DomainException($result['error']);
            }

            return new BalanceResult(
                balance: (float) ($result['Balance'] ?? 0),
                currency: $result['Currency'] ?? 'USD',
                reservedAmount: $result['ReservedAmount'] ?? null,
                availableAmount: $result['AvailableAmount'] ?? null,
            );
        } catch (\Exception $e) {
            throw new DomainException('Error getting balance: ' . $e->getMessage(), 0, $e);
        }
    }

    public function listDomains(int $page = 1, int $limit = 50): DomainCollection
    {
        try {
            $result = $this->client->GetDomainList([
                'page' => $page,
                'limit' => $limit,
            ]);

            $domains = [];
            if (isset($result['domains']) && is_array($result['domains'])) {
                foreach ($result['domains'] as $domainData) {
                    $domains[] = new DomainDto(
                        domain: $domainData['domain'] ?? '',
                        status: $domainData['status'] ?? 'active',
                        registrationDate: isset($domainData['registrationDate'])
                            ? new \DateTime($domainData['registrationDate'])
                            : null,
                        expirationDate: isset($domainData['expirationDate'])
                            ? new \DateTime($domainData['expirationDate'])
                            : null,
                        autoRenew: $domainData['autoRenew'] ?? false,
                        privacyProtection: $domainData['privacyProtection'] ?? false,
                    );
                }
            }

            return new DomainCollection(
                domains: $domains,
                total: $result['total'] ?? count($domains),
                page: $page,
                limit: $limit,
            );
        } catch (\Exception $e) {
            throw new DomainException('Error listing domains: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getTlds(): TldCollection
    {
        try {
            $result = $this->client->GetTldList();

            $tlds = [];
            if (isset($result['tlds']) && is_array($result['tlds'])) {
                foreach ($result['tlds'] as $tld) {
                    $tlds[] = [
                        'tld' => $tld,
                    ];
                }
            }

            return new TldCollection($tlds, count($tlds));
        } catch (\Exception $e) {
            throw new DomainException('Error getting TLDs: ' . $e->getMessage(), 0, $e);
        }
    }

    private function contactToArray($contact): array
    {
        if (!$contact) {
            return [];
        }

        return [
            'firstName' => $contact->firstName,
            'lastName' => $contact->lastName,
            'company' => $contact->company,
            'email' => $contact->email,
            'addressLine1' => $contact->addressLine1,
            'addressLine2' => $contact->addressLine2 ?? '',
            'city' => $contact->city,
            'state' => $contact->state ?? '',
            'country' => $contact->country,
            'zipCode' => $contact->zipCode,
            'phone' => $contact->phone,
            'fax' => $contact->fax ?? '',
        ];
    }
}