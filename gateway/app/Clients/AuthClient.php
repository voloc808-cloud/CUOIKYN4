<?php

namespace App\Clients;

use App\Exceptions\ServiceApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pims\Common\Clients\ServiceHttpClient;

final class AuthClient
{
    private readonly ServiceHttpClient $http;

    public function __construct()
    {
        $this->http = new ServiceHttpClient(
            new Client(['http_errors' => false]),
            Cache::store(),
            Log::getLogger(),
            'xác thực',
            (string) config('services.pims.auth_url')
        );
    }

    public function login(string $email, string $password): array
    {
        return $this->request('POST', '/api/v1/auth/login', ['json' => compact('email', 'password')]);
    }

    public function logout(string $token): void
    {
        $this->request('POST', '/api/v1/auth/logout', $this->authorized($token));
    }

    public function changePassword(string $token, array $data): void
    {
        $this->request('PUT', '/api/v1/auth/change-password', $this->authorized($token, ['json' => $data]));
    }

    public function users(string $token, string $search = '', int $page = 1): array
    {
        return $this->request('GET', '/api/v1/users', $this->authorized($token, ['query' => compact('search', 'page')]));
    }

    public function user(string $token, int $id): array
    {
        return $this->request('GET', "/api/v1/users/{$id}", $this->authorized($token));
    }

    public function createUser(string $token, array $data): array
    {
        return $this->request('POST', '/api/v1/users', $this->authorized($token, ['json' => $data]));
    }

    public function updateUser(string $token, int $id, array $data): array
    {
        return $this->request('PUT', "/api/v1/users/{$id}", $this->authorized($token, ['json' => $data]));
    }

    public function updateStatus(string $token, int $id, bool $isActive): array
    {
        return $this->request('PATCH', "/api/v1/users/{$id}/status", $this->authorized($token, ['json' => ['is_active' => $isActive]]));
    }

    public function deleteUser(string $token, int $id): void
    {
        $this->request('DELETE', "/api/v1/users/{$id}", $this->authorized($token));
    }

    private function authorized(string $token, array $options = []): array
    {
        $options['headers']['Authorization'] = "Bearer {$token}";
        return $options;
    }

    private function request(string $method, string $uri, array $options): mixed
    {
        $response = $this->http->request($method, $uri, $options);
        $payload = json_decode((string) $response->getBody(), true) ?: [];

        if ($response->getStatusCode() >= 400 || ($payload['status'] ?? null) === 'error') {
            throw new ServiceApiException(
                $payload['message'] ?? 'Dịch vụ xác thực trả về lỗi',
                $payload['errors'] ?? [],
                $response->getStatusCode()
            );
        }

        return $payload['data'] ?? null;
    }
}
