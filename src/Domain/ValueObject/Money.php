<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\ValueObject;

use Estratos\DomainNameApi\Domain\Exception\DomainException;

/**
 * Value Object para manejar cantidades monetarias.
 * Inmutable y con precisión decimal.
 */
class Money
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->validateCurrency($currency);
        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    private function validateCurrency(string $currency): void
    {
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new DomainException(sprintf('Invalid currency code: "%s"', $currency));
        }

        $validCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'CNY', 'BRL', 'MXN'];
        if (!in_array($currency, $validCurrencies, true)) {
            throw new DomainException(sprintf('Unsupported currency: "%s"', $currency));
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): Money
    {
        if ($this->currency !== $other->getCurrency()) {
            throw new DomainException('Cannot add money with different currencies');
        }
        return new Money($this->amount + $other->getAmount(), $this->currency);
    }

    public function subtract(Money $other): Money
    {
        if ($this->currency !== $other->getCurrency()) {
            throw new DomainException('Cannot subtract money with different currencies');
        }
        return new Money($this->amount - $other->getAmount(), $this->currency);
    }

    public function multiply(float $multiplier): Money
    {
        return new Money($this->amount * $multiplier, $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        if ($this->currency !== $other->getCurrency()) {
            throw new DomainException('Cannot compare money with different currencies');
        }
        return $this->amount > $other->getAmount();
    }

    public function isLessThan(Money $other): bool
    {
        if ($this->currency !== $other->getCurrency()) {
            throw new DomainException('Cannot compare money with different currencies');
        }
        return $this->amount < $other->getAmount();
    }

    public function isEqualTo(Money $other): bool
    {
        if ($this->currency !== $other->getCurrency()) {
            throw new DomainException('Cannot compare money with different currencies');
        }
        return $this->amount === $other->getAmount();
    }

    public function format(int $decimals = 2): string
    {
        return sprintf('%s %s', $this->currency, number_format($this->amount, $decimals));
    }

    public function __toString(): string
    {
        return $this->format();
    }
}