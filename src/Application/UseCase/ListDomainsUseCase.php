<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Application\UseCase;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\DomainCollection;

/**
 * Caso de uso para listar dominios.
 */
class ListDomainsUseCase
{
    public function __construct(
        private readonly DomainProviderInterface $provider
    ) {
    }

    public function execute(int $page = 1, int $limit = 50): DomainCollection
    {
        // Validar parámetros
        if ($page < 1) {
            throw new \InvalidArgumentException('Page must be at least 1');
        }

        if ($limit < 1 || $limit > 200) {
            throw new \InvalidArgumentException('Limit must be between 1 and 200');
        }

        return $this->provider->listDomains($page, $limit);
    }
}