<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Infrastructure\Provider;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\BalanceResult;
use Estratos\DomainNameApi\Domain\DTO\ContactDto;
use Estratos\DomainNameApi\Domain\DTO\DomainCheckResult;
use Estratos\DomainNameApi\Domain\DTO\DomainCollection;
use Estratos\DomainNameApi\Domain\DTO\DomainDto;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Estratos\DomainNameApi\Domain\DTO\RegisterResult;
use Estratos\DomainNameApi\Domain\DTO\TldCollection;
use Estratos\DomainNameApi\Domain\Exception\DomainException;
use Estratos\DomainNameApi\Domain\Exception\ProviderException;
use Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient;

/**
 * Proveedor REST para DomainResellerAPI.
 */
class RestProvider implements DomainProviderInterface
{
    private RestClient $client;
    private array $config;

    public function __construct(RestClient $client, array $config = [])
    {
        $this->client = $client;
        $this->config = array_merge([
            'default_nameservers' => ['ns1.domainnameapi.com', 'ns2.domainnameapi.com'],
        ], $config);
    }

    public function check(string $domain): DomainCheckResult
    {
        try {
            $response = $this->client->post('/api/v1/domains/search', [
                'domainName' => $domain,
            ]);

            if (isset($response['success']) && $response['success'] === false) {
                throw new DomainException($response['reason'] ?? 'Domain check failed');
            }

            $info = $response['info'] ?? [];

            return new DomainCheckResult(
                domain: $domain,
                available: isset($info['status']) && $info['status'] === 'available',
                price: $info['price'] ?? null,
                currency: $info['currency'] ?? 'USD',
                status: $info['status'] ?? null,
                reason: $info['reason'] ?? null,
                registrationPeriod: $info['period'] ?? null,
                isPremium: $info['isPremium'] ?? false,
            );
        } catch (ProviderException $e) {
            throw new DomainException('Error checking domain: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function register(RegisterDomainRequest $request): RegisterResult
    {
        try {
            $payload = [
                'domainName' => $request->domain,
                'period' => $request->period,
                'nameServers' => $request->nameservers ?: $this->config['default_nameservers'],
                'contacts' => [
                    $this->contactToArray($request->registrant, 'registrant'),
                    $this->contactToArray($request->admin, 'admin'),
                    $this->contactToArray($request->technical, 'tech'),
                    $this->contactToArray($request->billing, 'billing'),
                ],
                'useTrusteeContact' => $request->privacyProtection,
            ];

            if ($request->tldAttributes) {
                $payload['tldAttributes'] = $request->tldAttributes;
            }

            $response = $this->client->post('/api/v1/domains/register-with-contacts', $payload);

            return new RegisterResult(
                domain: $request->domain,
                success: $response['success'] ?? false,
                orderId: $response['domainId'] ?? null,
                message: $response['message'] ?? null,
                status: $response['status'] ?? null,
                expirationDate: isset($response['expirationDate'])
                    ? new \DateTime($response['expirationDate'])
                    : null,
            );
        } catch (ProviderException $e) {
            throw new DomainException('Error registering domain: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getBalance(): BalanceResult
    {
        try {
            $response = $this->client->get('/api/v1/deposit/accounts/me');

            return new BalanceResult(
                balance: $response['usdBalance'] ?? 0,
                currency: 'USD',
                reservedAmount: null,
                availableAmount: $response['usdBalance'] ?? null,
                resellerName: $response['resellerName'] ?? null,
                resellerId: $response['resellerId'] ?? null,
            );
        } catch (ProviderException $e) {
            throw new DomainException('Error getting balance: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function listDomains(int $page = 1, int $limit = 50): DomainCollection
    {
        try {
            $response = $this->client->get('/api/v1/domains', [
                'SkipCount' => ($page - 1) * $limit,
                'MaxResultCount' => $limit,
            ]);

            $domains = [];
            $items = $response['items'] ?? [];

            foreach ($items as $item) {
                $domains[] = new DomainDto(
                    domain: $item['domainName'] ?? '',
                    status: $this->mapStatus($item['status'] ?? 0),
                    registrationDate: isset($item['startDate'])
                        ? new \DateTime($item['startDate'])
                        : null,
                    expirationDate: isset($item['expirationDate'])
                        ? new \DateTime($item['expirationDate'])
                        : null,
                    transferDate: isset($item['transferDate'])
                        ? new \DateTime($item['transferDate'])
                        : null,
                    updatedDate: isset($item['updatedDate'])
                        ? new \DateTime($item['updatedDate'])
                        : null,
                    autoRenew: isset($item['renewalMode']) && $item['renewalMode'] === 'auto',
                    privacyProtection: $item['privacyProtectionStatus'] ?? false,
                    locked: $item['lockStatus'] ?? false,
                    nameservers: $item['nameServers'] ?? [],
                    contacts: $item['contacts'] ?? null,
                    remainingDays: $item['remainingDay'] ?? null,
                );
            }

            return new DomainCollection(
                domains: $domains,
                total: $response['totalCount'] ?? count($domains),
                page: $page,
                limit: $limit,
            );
        } catch (ProviderException $e) {
            throw new DomainException('Error listing domains: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getTlds(): TldCollection
    {
        try {
            $response = $this->client->get('/api/v1/products/tlds', [
                'MaxResultCount' => 1000,
            ]);

            $tlds = [];
            $items = $response['items'] ?? [];

            foreach ($items as $item) {
                $tlds[] = [
                    'tld' => $item['name'] ?? '',
                    'minRegistrationPeriod' => $item['minRegistrationPeriod'] ?? 1,
                    'maxRegistrationPeriod' => $item['maxRegistrationPeriod'] ?? 10,
                    'registrationPeriods' => $item['registrationPeriods'] ?? [1, 2, 3, 5, 10],
                    'prices' => $item['prices'] ?? [],
                    'attributes' => $item['attributes'] ?? [],
                ];
            }

            return new TldCollection($tlds, count($tlds));
        } catch (ProviderException $e) {
            throw new DomainException('Error getting TLDs: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function contactToArray(ContactDto $contact, string $type): array
    {
        return [
            'contactType' => $type,
            'firstName' => $contact->firstName,
            'lastName' => $contact->lastName,
            'companyName' => $contact->company,
            'eMail' => $contact->email,
            'address' => $contact->addressLine1 . ($contact->addressLine2 ? ' ' . $contact->addressLine2 : ''),
            'phoneCountryCode' => $this->extractCountryCode($contact->phone),
            'phone' => $this->extractPhoneNumber($contact->phone),
            'faxCountryCode' => $this->extractCountryCode($contact->fax),
            'fax' => $this->extractPhoneNumber($contact->fax),
            'postalCode' => $contact->zipCode,
            'country' => $contact->country,
            'city' => $contact->city,
            'state' => $contact->state,
        ];
    }

    private function extractCountryCode(string $phone): string
    {
        if (preg_match('/^\+(\d+)/', $phone, $matches)) {
            return $matches[1];
        }
        return '1';
    }

    private function extractPhoneNumber(string $phone): string
    {
        return preg_replace('/^\+?\d+/', '', $phone) ?: $phone;
    }

    private function mapStatus(int $status): string
    {
        $statusMap = [
            0 => 'pending',
            1 => 'active',
            2 => 'inactive',
            3 => 'suspended',
            4 => 'deleted',
            7 => 'transfer_pending',
            8 => 'transfer_failed',
            9 => 'expired',
            11 => 'pending_verification',
            12 => 'pending_document',
            15 => 'pending_renewal',
            18 => 'pending_transfer',
            19 => 'pending_delete',
        ];

        return $statusMap[$status] ?? 'unknown';
    }
}