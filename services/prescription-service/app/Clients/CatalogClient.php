<?php

namespace App\Clients;

use App\Exceptions\BusinessException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;

final class CatalogClient
{
    private readonly ServiceHttpClient $http;
    public function __construct() { $this->http = new ServiceHttpClient(new Client(['http_errors' => false]), Cache::store(), Log::getLogger(), 'danh mục', (string) env('SERVICE_CATALOG_URL')); }
    public function medicine(int $id, string $token): array
    {
        $response = $this->http->request('GET', "/internal/v1/medicines/{$id}", ['headers' => $this->headers($token)]);
        $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() !== 200) throw new BusinessException("Không tìm thấy thuốc {$id}", 'MEDICINE_NOT_FOUND', 422);
        return $payload['data'];
    }
    private function headers(string $token): array { return ['Authorization' => "Bearer {$token}", 'X-Internal-Key' => (string) env('INTERNAL_API_KEY')]; }
}
