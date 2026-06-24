<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

/**
 * Resultado de verificación de disponibilidad de dominio.
 */
class DomainCheckResult
{
    public function __construct(
        public string $domain,
        public bool $available,
        public ?float $price = null,
        public ?string $currency = null,
        public ?string $status = null,
        public ?string $reason = null,
        public ?int $registrationPeriod = null,
        public ?bool $isPremium = null,
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function getFormattedPrice(): ?string
    {
        if ($this->price === null || $this->currency === null) {
            return null;
        }
        return sprintf('%s %s', $this->currency, number_format($this->price, 2));
    }
}