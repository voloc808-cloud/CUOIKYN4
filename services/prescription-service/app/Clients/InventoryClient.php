<?php

namespace App\Clients;

use App\Exceptions\BusinessException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class InventoryClient
{
    private readonly ServiceHttpClient $http;
    public function __construct() { $this->http = new ServiceHttpClient(new Client(['http_errors' => false]), Cache::store(), Log::getLogger(), 'kho', (string) env('SERVICE_INVENTORY_URL')); }
    public function check(array $items, string $token): array { return $this->post('/internal/v1/stock/check', ['items' => $items], $token); }
    public function deduct(int $id, array $items, string $token): array { return $this->post('/internal/v1/stock/deduct-fefo', ['prescription_id' => $id, 'items' => $items], $token); }
    public function restore(int $id, string $token): array { return $this->post('/internal/v1/stock/restore', ['prescription_id' => $id], $token); }
    public function deductionStatus(int $id, string $token): string
    {
        $response = $this->http->request('GET', "/internal/v1/stock/prescriptions/{$id}", ['headers' => ['Authorization' => "Bearer {$token}", 'X-Internal-Key' => (string) env('INTERNAL_API_KEY')]]);
        $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() !== 200 || ($payload['status'] ?? '') !== 'success') {
            throw new BusinessException('Không thể đối soát giao dịch kho', 'INVENTORY_RECONCILIATION_FAILED', 503);
        }

        return (string) ($payload['data']['status'] ?? 'UNKNOWN');
    }
    private function post(string $uri, array $data, string $token): array
    {
        $response = $this->http->request('POST', $uri, ['headers' => ['Authorization' => "Bearer {$token}", 'X-Internal-Key' => (string) env('INTERNAL_API_KEY')], 'json' => $data]);
        $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() >= 500) throw new ServiceUnavailableException('kho');
        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? '') !== 'success') throw new BusinessException($payload['message'] ?? 'Dịch vụ kho từ chối yêu cầu', $payload['code'] ?? 'INVENTORY_ERROR', $response->getStatusCode() ?: 503);
        return $payload['data'];
    }
}
