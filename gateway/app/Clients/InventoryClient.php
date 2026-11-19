<?php

namespace App\Clients;

use App\Exceptions\ServiceApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;

final class InventoryClient
{
    private readonly ServiceHttpClient $http;

    public function __construct()
    {
        $this->http = new ServiceHttpClient(new Client(['http_errors' => false]), Cache::store(), Log::getLogger(), 'kho', (string) config('services.pims.inventory_url'));
    }

    public function receipts(string $token, string $search = '', int $page = 1): array { return $this->request('GET', '/api/v1/import-receipts', $token, ['query' => compact('search', 'page')]); }
    public function receipt(string $token, int $id): array { return $this->request('GET', "/api/v1/import-receipts/{$id}", $token); }
    public function createReceipt(string $token, array $data): array { return $this->request('POST', '/api/v1/import-receipts', $token, ['json' => $data]); }
    public function batches(string $token, string $search = '', int $page = 1): array { return $this->request('GET', '/api/v1/batches', $token, ['query' => compact('search', 'page')]); }
    public function movements(string $token, int $page = 1): array { return $this->request('GET', '/api/v1/stock-movements', $token, ['query' => compact('page')]); }
    public function disposals(string $token, int $page = 1): array { return $this->request('GET', '/api/v1/disposal-receipts', $token, ['query' => compact('page')]); }
    public function disposal(string $token, int $id): array { return $this->request('GET', "/api/v1/disposal-receipts/{$id}", $token); }
    public function createDisposal(string $token, array $data): array { return $this->request('POST', '/api/v1/disposal-receipts', $token, ['json' => $data]); }
    public function alerts(string $token): array { return $this->request('GET', '/api/v1/alerts', $token); }
    public function updateAlertSettings(string $token, int $days): array { return $this->request('PUT', '/api/v1/alert-settings', $token, ['json' => ['expiry_warning_days' => $days]]); }

    public function medicineHasBatches(string $token, int $medicineId): bool
    {
        $data = $this->request('GET', "/internal/v1/medicines/{$medicineId}/has-batches", $token, [
            'headers' => ['X-Internal-Key' => (string) env('INTERNAL_API_KEY')],
        ]);
        return (bool) ($data['has_batches'] ?? false);
    }

    private function request(string $method, string $uri, string $token, array $options = []): mixed
    {
        $options['headers']['Authorization'] = "Bearer {$token}";
        $response = $this->http->request($method, $uri, $options);
        $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? null) === 'error') {
            throw new ServiceApiException($payload['message'] ?? 'Dịch vụ kho trả về lỗi', $payload['errors'] ?? [], $response->getStatusCode());
        }
        return $payload['data'] ?? null;
    }
}
