<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\Exception;

/**
 * Excepción lanzada cuando ocurre un error en el proveedor.
 */
class ProviderException extends DomainException
{
    private ?string $providerType;
    private ?string $endpoint;

    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
        ?string $providerType = null,
        ?string $endpoint = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->providerType = $providerType;
        $this->endpoint = $endpoint;
    }

    public function getProviderType(): ?string
    {
        return $this->providerType;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }
}