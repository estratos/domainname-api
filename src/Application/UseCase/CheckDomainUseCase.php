<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Application\UseCase;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\DomainCheckResult;

/**
 * Caso de uso para verificar disponibilidad de dominio.
 */
class CheckDomainUseCase
{
    public function __construct(
        private readonly DomainProviderInterface $provider
    ) {
    }

    public function execute(string $domain): DomainCheckResult
    {
        // Validación básica
        if (empty($domain) || !str_contains($domain, '.')) {
            throw new \InvalidArgumentException('Invalid domain name format');
        }

        return $this->provider->check($domain);
    }
}