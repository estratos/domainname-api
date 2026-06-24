<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Application\Service;

use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\Contract\ProviderFactoryInterface;
use Estratos\DomainNameApi\Domain\Exception\InvalidConfigurationException;

/**
 * Manager de proveedores. Permite seleccionar y cambiar el proveedor activo.
 */
class ProviderManager
{
    private ?DomainProviderInterface $currentProvider = null;
    private string $providerType;
    private ProviderFactoryInterface $factory;

    public function __construct(ProviderFactoryInterface $factory, string $defaultProvider = 'rest')
    {
        $this->factory = $factory;
        $this->providerType = $defaultProvider;
    }

    /**
     * Obtiene el proveedor actual. Si no está inicializado, lo crea.
     */
    public function getProvider(): DomainProviderInterface
    {
        if ($this->currentProvider === null) {
            $this->currentProvider = $this->factory->create($this->providerType);
        }
        return $this->currentProvider;
    }

    /**
     * Cambia el proveedor activo.
     */
    public function setProvider(string $type): self
    {
        $this->providerType = $type;
        $this->currentProvider = null;
        return $this;
    }

    /**
     * Obtiene el tipo de proveedor actual.
     */
    public function getProviderType(): string
    {
        return $this->providerType;
    }

    /**
     * Verifica si un tipo de proveedor está soportado.
     */
    public function isProviderSupported(string $type): bool
    {
        return in_array($type, ['rest', 'soap'], true);
    }

    /**
     * Valida y cambia el proveedor. Lanza excepción si no es válido.
     */
    public function switchProvider(string $type): self
    {
        if (!$this->isProviderSupported($type)) {
            throw new InvalidConfigurationException(sprintf('Provider type "%s" is not supported', $type));
        }
        return $this->setProvider($type);
    }
}