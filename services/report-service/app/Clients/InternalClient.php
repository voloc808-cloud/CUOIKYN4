<?php
namespace App\Clients;
use App\Exceptions\BusinessException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;
final class InternalClient
{
    public function get(string $service, string $baseUrl, string $uri, string $token, array $query = []): array
    {
        $http = new ServiceHttpClient(new Client(['http_errors' => false]), Cache::store(), Log::getLogger(), $service, $baseUrl);
        $response = $http->request('GET', $uri, ['headers' => ['Authorization' => "Bearer {$token}", 'X-Internal-Key' => (string) env('INTERNAL_API_KEY')], 'query' => $query]);
        $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? '') !== 'success') throw new BusinessException($payload['message'] ?? "Dịch vụ {$service} trả về lỗi", 'DOWNSTREAM_ERROR', $response->getStatusCode() ?: 503);
        return $payload['data'];
    }
}
