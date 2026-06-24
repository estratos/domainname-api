<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\Contract;

interface ProviderFactoryInterface
{
    /**
     * Crea una instancia del proveedor según el tipo especificado.
     */
    public function create(string $type): DomainProviderInterface;
}