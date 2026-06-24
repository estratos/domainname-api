<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

/**
 * Resultado de registro de dominio.
 */
class RegisterResult
{
    public function __construct(
        public string $domain,
        public bool $success,
        public ?string $orderId = null,
        public ?string $transactionId = null,
        public ?string $message = null,
        public ?array $errors = null,
        public ?string $status = null,
        public ?\DateTime $expirationDate = null,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorMessage(): ?string
    {
        if ($this->errors && !empty($this->errors)) {
            return implode(', ', $this->errors);
        }
        return $this->message;
    }
}