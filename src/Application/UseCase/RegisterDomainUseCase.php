<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Application\UseCase;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Estratos\DomainNameApi\Domain\DTO\RegisterResult;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Caso de uso para registrar un dominio.
 */
class RegisterDomainUseCase
{
    public function __construct(
        private readonly DomainProviderInterface $provider,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function execute(RegisterDomainRequest $request): RegisterResult
    {
        // Validar el request
        $errors = $this->validator->validate($request);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errorMessages));
        }

        // Validar nameservers
        if (!$request->hasValidNameservers()) {
            throw new \InvalidArgumentException('At least 2 nameservers are required');
        }

        return $this->provider->register($request);
    }
}