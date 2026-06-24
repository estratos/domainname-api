<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

/**
 * DTO para información de un dominio.
 */
class DomainDto
{
    public function __construct(
        public string $domain,
        public string $status,
        public ?\DateTime $registrationDate = null,
        public ?\DateTime $expirationDate = null,
        public ?\DateTime $transferDate = null,
        public ?\DateTime $updatedDate = null,
        public bool $autoRenew = false,
        public bool $privacyProtection = false,
        public bool $locked = false,
        public array $nameservers = [],
        public ?array $contacts = null,
        public ?int $remainingDays = null,
        public ?string $authCode = null,
    ) {
    }

    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expirationDate) {
            return null;
        }
        $now = new \DateTime();
        return (int) $now->diff($this->expirationDate)->format('%r%a');
    }

    public function isExpired(): bool
    {
        if (!$this->expirationDate) {
            return false;
        }
        return new \DateTime() > $this->expirationDate;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}