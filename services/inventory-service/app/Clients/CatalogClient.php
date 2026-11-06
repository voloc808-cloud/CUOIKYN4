<?php

namespace App\Clients;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class CatalogClient
{
    private readonly ServiceHttpClient $http;

    public function __construct()
    {
        $this->http = new ServiceHttpClient(
            new Client(['http_errors' => false]),
            Cache::store(),
            Log::getLogger(),
            'danh mục',
            (string) env('SERVICE_CATALOG_URL')
        );
    }

    public function medicine(int $id, string $token): array
    {
        return $this->get("/internal/v1/medicines/{$id}", $token);
    }

    public function supplier(int $id, string $token): array
    {
        return $this->get("/internal/v1/suppliers/{$id}", $token);
    }

    public function stockThresholds(string $token): array
    {
        return $this->get('/internal/v1/medicines/stock-thresholds', $token);
    }

    private function get(string $uri, string $token): array
    {
        $response = $this->http->request('GET', $uri, ['headers' => [
            'Authorization' => "Bearer {$token}",
            'X-Internal-Key' => (string) env('INTERNAL_API_KEY'),
        ]]);
        $payload = json_decode((string) $response->getBody(), true);
        if ($response->getStatusCode() !== 200 || ($payload['status'] ?? null) !== 'success') {
            throw new ServiceUnavailableException('danh mục');
        }
        return $payload['data'];
    }
}
