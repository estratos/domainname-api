<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Application\UseCase;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\TldCollection;

/**
 * Caso de uso para obtener la lista de TLDs disponibles.
 */
class GetTldsUseCase
{
    public function __construct(
        private readonly DomainProviderInterface $provider
    ) {
    }

    public function execute(): TldCollection
    {
        return $this->provider->getTlds();
    }
}