<?php
namespace App\Clients;
use App\Exceptions\ServiceApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;
final class ReportClient
{
    private readonly ServiceHttpClient $http;
    public function __construct() { $this->http = new ServiceHttpClient(new Client(['http_errors' => false]), Cache::store(), Log::getLogger(), 'báo cáo', (string) config('services.pims.report_url')); }
    public function stock(string $token, string $from, string $to): array { return $this->json('/api/v1/reports/stock', $token, compact('from','to')); }
    public function download(string $token, string $format, string $from, string $to): array
    {
        $response = $this->http->request('GET', "/api/v1/reports/stock.{$format}", ['headers' => ['Authorization' => "Bearer {$token}"], 'query' => compact('from','to')]);
        if ($response->getStatusCode() >= 400) { $payload = json_decode((string) $response->getBody(), true) ?: []; throw new ServiceApiException($payload['message'] ?? 'Không thể xuất báo cáo', $payload['errors'] ?? [], $response->getStatusCode()); }
        return ['content' => (string) $response->getBody(), 'type' => $response->getHeaderLine('Content-Type')];
    }
    private function json(string $uri, string $token, array $query): array
    {
        $response = $this->http->request('GET', $uri, ['headers' => ['Authorization' => "Bearer {$token}"], 'query' => $query]); $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? '') === 'error') throw new ServiceApiException($payload['message'] ?? 'Dịch vụ báo cáo trả về lỗi', $payload['errors'] ?? [], $response->getStatusCode()); return $payload['data'];
    }
}
