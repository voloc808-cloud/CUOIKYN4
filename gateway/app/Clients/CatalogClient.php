<?php

namespace App\Clients;

use App\Exceptions\ServiceApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;

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
            (string) config('services.pims.catalog_url')
        );
    }

    public function medicines(string $token, string $search = '', int $page = 1): array
    {
        return $this->request('GET', '/api/v1/medicines', $token, ['query' => compact('search', 'page')]);
    }

    public function medicine(string $token, int $id): array
    {
        return $this->request('GET', "/api/v1/medicines/{$id}", $token);
    }

    public function createMedicine(string $token, array $data): array
    {
        return $this->request('POST', '/api/v1/medicines', $token, ['json' => $data]);
    }

    public function updateMedicine(string $token, int $id, array $data): array
    {
        return $this->request('PUT', "/api/v1/medicines/{$id}", $token, ['json' => $data]);
    }

    public function deleteMedicine(string $token, int $id): void
    {
        $this->request('DELETE', "/internal/v1/medicines/{$id}", $token, [
            'headers' => ['X-Internal-Key' => (string) env('INTERNAL_API_KEY')],
        ]);
    }

    public function units(string $token): array
    {
        return $this->request('GET', '/api/v1/units', $token);
    }

    public function suppliers(string $token, string $search = '', int $page = 1): array
    {
        return $this->request('GET', '/api/v1/suppliers', $token, ['query' => compact('search', 'page')]);
    }

    public function supplier(string $token, int $id): array
    {
        return $this->request('GET', "/api/v1/suppliers/{$id}", $token);
    }

    public function createSupplier(string $token, array $data): array
    {
        return $this->request('POST', '/api/v1/suppliers', $token, ['json' => $data]);
    }

    public function updateSupplier(string $token, int $id, array $data): array
    {
        return $this->request('PUT', "/api/v1/suppliers/{$id}", $token, ['json' => $data]);
    }

    public function deleteSupplier(string $token, int $id): void
    {
        $this->request('DELETE', "/api/v1/suppliers/{$id}", $token);
    }

    private function request(string $method, string $uri, string $token, array $options = []): mixed
    {
        $options['headers']['Authorization'] = "Bearer {$token}";
        $response = $this->http->request($method, $uri, $options);
        $payload = json_decode((string) $response->getBody(), true) ?: [];

        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? null) === 'error') {
            throw new ServiceApiException($payload['message'] ?? 'Dịch vụ danh mục trả về lỗi', $payload['errors'] ?? [], $response->getStatusCode());
        }

        return $payload['data'] ?? null;
    }
}
