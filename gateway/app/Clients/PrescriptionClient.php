<?php
namespace App\Clients;
use App\Exceptions\ServiceApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;
final class PrescriptionClient
{
    private readonly ServiceHttpClient $http;
    public function __construct() { $this->http = new ServiceHttpClient(new Client(['http_errors' => false]), Cache::store(), Log::getLogger(), 'đơn thuốc', (string) config('services.pims.prescription_url')); }
    public function list(string $token, string $status = '', int $page = 1): array { return $this->request('GET', '/api/v1/prescriptions', $token, ['query' => compact('status', 'page')]); }
    public function show(string $token, int $id): array { return $this->request('GET', "/api/v1/prescriptions/{$id}", $token); }
    public function create(string $token, array $data): array { return $this->request('POST', '/api/v1/prescriptions', $token, ['json' => $data]); }
    public function approve(string $token, int $id): array { return $this->request('POST', "/api/v1/prescriptions/{$id}/approve", $token); }
    public function reject(string $token, int $id, string $reason): array { return $this->request('POST', "/api/v1/prescriptions/{$id}/reject", $token, ['json' => compact('reason')]); }
    private function request(string $method, string $uri, string $token, array $options = []): mixed
    {
        $options['headers']['Authorization'] = "Bearer {$token}"; $response = $this->http->request($method, $uri, $options); $payload = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? '') === 'error') throw new ServiceApiException($payload['message'] ?? 'Dịch vụ đơn thuốc trả về lỗi', $payload['errors'] ?? [], $response->getStatusCode());
        return $payload['data'];
    }
}
