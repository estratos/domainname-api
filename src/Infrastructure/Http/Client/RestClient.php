<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Infrastructure\Http\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Estratos\DomainNameApi\Domain\Exception\ProviderException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cliente HTTP para la API REST de DomainResellerAPI.
 */
class RestClient implements RestClientInterface
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $resellerId;
    private string $endpoint;
    private bool $verifySsl;
    private int $timeout;
    private int $retryAttempts;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiKey,
        string $resellerId,
        string $endpoint = 'https://api.domainresellerapi.com',
        bool $verifySsl = true,
        int $timeout = 30,
        int $retryAttempts = 3
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->resellerId = $resellerId;
        $this->endpoint = rtrim($endpoint, '/');
        $this->verifySsl = $verifySsl;
        $this->timeout = $timeout;
        $this->retryAttempts = $retryAttempts;
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, $query);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, [], $data);
    }

    public function put(string $path, array $data = []): array
    {
        return $this->request('PUT', $path, [], $data);
    }

    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, $query);
    }

    private function request(string $method, string $path, array $query = [], array $data = []): array
    {
        $url = $this->buildUrl($path, $query);
        $options = $this->getOptions($data);

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->httpClient->request($method, $url, $options);
                return $this->handleResponse($response);
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt >= $this->retryAttempts) {
                    break;
                }

                // Esperar antes de reintentar (backoff exponencial)
                usleep(100000 * pow(2, $attempt));
            }
        }

        throw new ProviderException(
            sprintf('Request failed after %d attempts: %s', $this->retryAttempts, $lastException?->getMessage()),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $lastException
        );
    }

    private function buildUrl(string $path, array $query = []): string
    {
        $url = $this->endpoint . '/' . ltrim($path, '/');

        if (!empty($query)) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }

    private function getOptions(array $data = []): array
    {
        $headers = [
            'X-API-KEY' => $this->apiKey,
            '__reseller' => $this->resellerId,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $options = [
            'headers' => $headers,
            'verify_peer' => $this->verifySsl,
            'timeout' => $this->timeout,
        ];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $options;
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        // Si la respuesta está vacía
        if (empty($content)) {
            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'statusCode' => $statusCode,
            ];
        }

        // Intentar decodificar JSON
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Respuesta no JSON
            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'raw' => $content,
                'statusCode' => $statusCode,
            ];
        }

        // Verificar si hay error en la respuesta
        if ($statusCode >= 400) {
            $errorMessage = $this->extractErrorMessage($data);
            $errorCode = $data['error']['code'] ?? null;

            throw new ProviderException(
                sprintf('API Error [%d]: %s', $statusCode, $errorMessage),
                $statusCode,
                null,
                'rest',
                $this->endpoint
            );
        }

        return $data;
    }

    private function extractErrorMessage(array $data): string
    {
        // Diferentes formatos de error posibles
        if (isset($data['error']['message'])) {
            return $data['error']['message'];
        }

        if (isset($data['error'])) {
            return is_string($data['error']) ? $data['error'] : json_encode($data['error']);
        }

        if (isset($data['message'])) {
            return $data['message'];
        }

        if (isset($data['title'])) {
            return $data['title'];
        }

        return 'Unknown error occurred';
    }
}