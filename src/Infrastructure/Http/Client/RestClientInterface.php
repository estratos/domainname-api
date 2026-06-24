<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Infrastructure\Http\Client;

/**
 * Interface para el cliente REST de la API de dominios.
 */
interface RestClientInterface
{
    public function get(string $path, array $query = []): array;

    public function post(string $path, array $data = []): array;

    public function put(string $path, array $data = []): array;

    public function delete(string $path, array $query = []): array;
}