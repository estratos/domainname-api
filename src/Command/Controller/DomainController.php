<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Controller;

use Estratos\DomainNameApi\Application\UseCase\CheckDomainUseCase;
use Estratos\DomainNameApi\Application\UseCase\GetBalanceUseCase;
use Estratos\DomainNameApi\Application\UseCase\GetTldsUseCase;
use Estratos\DomainNameApi\Application\UseCase\ListDomainsUseCase;
use Estratos\DomainNameApi\Application\UseCase\RegisterDomainUseCase;
use Estratos\DomainNameApi\Domain\DTO\ContactDto;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/domain', name: 'domain_api_')]
class DomainController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/{domain}/check', name: 'check', methods: ['GET'])]
    public function check(string $domain, CheckDomainUseCase $useCase): JsonResponse
    {
        try {
            // Validar formato del dominio
            if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z]{2,})$/', $domain)) {
                return $this->json(
                    ['error' => 'Invalid domain name format'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $result = $useCase->execute($domain);

            return $this->json([
                'success' => true,
                'data' => [
                    'domain' => $result->domain,
                    'available' => $result->available,
                    'price' => $result->price,
                    'currency' => $result->currency,
                    'status' => $result->status,
                    'reason' => $result->reason,
                    'registrationPeriod' => $result->registrationPeriod,
                    'isPremium' => $result->isPremium,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(
                ['success' => false, 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/balance', name: 'balance', methods: ['GET'])]
    public function balance(GetBalanceUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute();

            return $this->json([
                'success' => true,
                'data' => [
                    'balance' => $result->balance,
                    'currency' => $result->currency,
                    'availableAmount' => $result->availableAmount,
                    'reservedAmount' => $result->reservedAmount,
                    'resellerId' => $result->resellerId,
                    'resellerName' => $result->resellerName,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(
                ['success' => false, 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, ListDomainsUseCase $useCase): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 50);

            if ($page < 1) {
                return $this->json(
                    ['error' => 'Page must be at least 1'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if ($limit < 1 || $limit > 200) {
                return $this->json(
                    ['error' => 'Limit must be between 1 and 200'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $result = $useCase->execute($page, $limit);

            $domains = array_map(function ($domain) {
                return [
                    'domain' => $domain->domain,
                    'status' => $domain->status,
                    'registrationDate' => $domain->registrationDate?->format('Y-m-d H:i:s'),
                    'expirationDate' => $domain->expirationDate?->format('Y-m-d H:i:s'),
                    'autoRenew' => $domain->autoRenew,
                    'privacyProtection' => $domain->privacyProtection,
                    'locked' => $domain->locked,
                    'remainingDays' => $domain->remainingDays,
                    'nameservers' => $domain->nameservers,
                ];
            }, $result->getDomains());

            return $this->json([
                'success' => true,
                'data' => [
                    'items' => $domains,
                    'total' => $result->total,
                    'page' => $result->page,
                    'limit' => $result->limit,
                    'totalPages' => $result->getTotalPages(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(
                ['success' => false, 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/tlds', name: 'tlds', methods: ['GET'])]
    public function tlds(GetTldsUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute();

            return $this->json([
                'success' => true,
                'data' => [
                    'items' => $result->getTlds(),
                    'total' => $result->total,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(
                ['success' => false, 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, RegisterDomainUseCase $useCase): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return $this->json(
                    ['error' => 'Invalid JSON payload'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Validar campos requeridos
            $requiredFields = ['domain', 'period', 'registrant', 'admin', 'technical', 'billing'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    return $this->json(
                        ['error' => sprintf('Missing required field: %s', $field)],
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }

            // Crear DTOs de contacto
            $registrant = $this->createContact($data['registrant']);
            $admin = $this->createContact($data['admin']);
            $technical = $this->createContact($data['technical']);
            $billing = $this->createContact($data['billing']);

            // Validar contactos
            $contactErrors = [];
            foreach (['registrant', 'admin', 'technical', 'billing'] as $type) {
                $contact = $$type;
                $errors = $this->validator->validate($contact);
                if (count($errors) > 0) {
                    $contactErrors[$type] = (string) $errors;
                }
            }

            if (!empty($contactErrors)) {
                return $this->json(
                    ['error' => 'Invalid contact data', 'details' => $contactErrors],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $registerRequest = new RegisterDomainRequest(
                domain: $data['domain'],
                period: (int) $data['period'],
                registrant: $registrant,
                admin: $admin,
                technical: $technical,
                billing: $billing,
                nameservers: $data['nameservers'] ?? [],
                privacyProtection: $data['privacyProtection'] ?? false,
                authCode: $data['authCode'] ?? null,
                autoRenew: $data['autoRenew'] ?? null,
                tldAttributes: $data['tldAttributes'] ?? null,
                useTrusteeContact: $data['useTrusteeContact'] ?? false,
            );

            $result = $useCase->execute($registerRequest);

            $statusCode = $result->isSuccess() ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;

            return $this->json([
                'success' => $result->isSuccess(),
                'data' => [
                    'domain' => $result->domain,
                    'orderId' => $result->orderId,
                    'transactionId' => $result->transactionId,
                    'message' => $result->message,
                    'status' => $result->status,
                    'expirationDate' => $result->expirationDate?->format('Y-m-d'),
                ],
                'errors' => $result->errors,
            ], $statusCode);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->json(
                ['success' => false, 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function createContact(array $data): ContactDto
    {
        return new ContactDto(
            firstName: $data['firstName'] ?? '',
            lastName: $data['lastName'] ?? '',
            company: $data['company'] ?? '',
            email: $data['email'] ?? '',
            addressLine1: $data['addressLine1'] ?? '',
            addressLine2: $data['addressLine2'] ?? '',
            city: $data['city'] ?? '',
            state: $data['state'] ?? '',
            country: $data['country'] ?? '',
            zipCode: $data['zipCode'] ?? '',
            phone: $data['phone'] ?? '',
            fax: $data['fax'] ?? '',
            phoneCountryCode: $data['phoneCountryCode'] ?? '',
        );
    }
}