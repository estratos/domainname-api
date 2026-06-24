<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Application\UseCase;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\BalanceResult;

/**
 * Caso de uso para obtener el saldo de la cuenta.
 */
class GetBalanceUseCase
{
    public function __construct(
        private readonly DomainProviderInterface $provider
    ) {
    }

    public function execute(): BalanceResult
    {
        return $this->provider->getBalance();
    }
}