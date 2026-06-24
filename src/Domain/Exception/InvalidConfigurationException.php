<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\Exception;

/**
 * Excepción lanzada cuando la configuración es inválida.
 */
class InvalidConfigurationException extends DomainException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}