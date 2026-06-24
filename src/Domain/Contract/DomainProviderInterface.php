<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\Contract;

use Estratos\DomainNameApi\Domain\DTO\BalanceResult;
use Estratos\DomainNameApi\Domain\DTO\DomainCheckResult;
use Estratos\DomainNameApi\Domain\DTO\DomainCollection;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Estratos\DomainNameApi\Domain\DTO\RegisterResult;
use Estratos\DomainNameApi\Domain\DTO\TldCollection;

/**
 * Interface principal para proveedores de dominios.
 * Define las operaciones básicas que cualquier proveedor debe implementar.
 */
interface DomainProviderInterface
{
    /**
     * Verifica la disponibilidad de un dominio.
     */
    public function check(string $domain): DomainCheckResult;

    /**
     * Registra un nuevo dominio.
     */
    public function register(RegisterDomainRequest $request): RegisterResult;

    /**
     * Obtiene el saldo de la cuenta.
     */
    public function getBalance(): BalanceResult;

    /**
     * Lista los dominios del reseller.
     */
    public function listDomains(int $page = 1, int $limit = 50): DomainCollection;

    /**
     * Obtiene la lista de TLDs disponibles.
     */
    public function getTlds(): TldCollection;
}