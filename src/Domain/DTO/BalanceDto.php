<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

/**
 * Resultado de consulta de saldo.
 */
class BalanceResult
{
    public function __construct(
        public float $balance,
        public string $currency = 'USD',
        public ?float $reservedAmount = null,
        public ?float $availableAmount = null,
        public ?string $resellerName = null,
        public ?string $resellerId = null,
    ) {
    }

    public function getFormattedBalance(): string
    {
        return sprintf('%s %s', $this->currency, number_format($this->balance, 2));
    }

    public function hasSufficientFunds(float $amount): bool
    {
        return $this->balance >= $amount;
    }
}