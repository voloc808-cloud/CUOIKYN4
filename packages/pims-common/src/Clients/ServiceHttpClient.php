<?php

declare(strict_types=1);

namespace Pims\Common\Clients;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Pims\Common\Exceptions\ServiceUnavailableException;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;

final class ServiceHttpClient
{
    private const FAILURE_LIMIT = 3;
    private const CIRCUIT_TTL_SECONDS = 30;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly CacheRepository $cache,
        private readonly LoggerInterface $logger,
        private readonly string $serviceName,
        private readonly string $baseUrl
    ) {
    }

    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $method = strtoupper($method);
        $failureKey = "circuit:{$this->serviceName}:failures";
        $openKey = "circuit:{$this->serviceName}:open";

        if ($this->cache->has($openKey)) {
            throw new ServiceUnavailableException($this->serviceName);
        }

        $options['timeout'] = 5;
        $options['headers']['X-Correlation-ID'] = app()->bound('correlation_id')
            ? app('correlation_id')
            : 'unknown';
        $maxAttempts = $method === 'GET' ? 3 : 1;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = $this->client->request($method, rtrim($this->baseUrl, '/').'/'.ltrim($uri, '/'), $options);
                $this->cache->forget($failureKey);

                return $response;
            } catch (GuzzleException $exception) {
                $this->logger->warning('service_request_failed', [
                    'service' => $this->serviceName,
                    'method' => $method,
                    'uri' => $uri,
                    'attempt' => $attempt,
                    'correlation_id' => $options['headers']['X-Correlation-ID'],
                ]);

                if ($attempt === $maxAttempts) {
                    $failures = (int) $this->cache->increment($failureKey);
                    $this->cache->put($failureKey, $failures, self::CIRCUIT_TTL_SECONDS);
                    if ($failures >= self::FAILURE_LIMIT) {
                        $this->cache->put($openKey, true, self::CIRCUIT_TTL_SECONDS);
                    }

                    throw new ServiceUnavailableException($this->serviceName, $exception);
                }
            }
        }

        throw new ServiceUnavailableException($this->serviceName);
    }
}
