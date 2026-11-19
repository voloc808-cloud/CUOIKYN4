<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly JWTAuth $jwt
    ) {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->users->findActiveByEmail($email);
        if ($user === null || !Hash::check($password, $user->password)) {
            throw new BusinessException('Email hoặc mật khẩu không đúng', 'INVALID_CREDENTIALS', 401);
        }

        $token = $this->jwt->fromUser($user);
        $user->update(['last_login_at' => \Illuminate\Support\Carbon::now()]);

        return $this->tokenPayload($token, $user);
    }

    public function logout(): void
    {
        $this->jwt->parseToken()->invalidate();
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->users->findOrFail($userId);
        if (!Hash::check($currentPassword, $user->password)) {
            throw new BusinessException('Mật khẩu hiện tại không đúng', 'CURRENT_PASSWORD_INVALID');
        }

        $user->update(['password' => Hash::make($newPassword)]);
    }

    private function tokenPayload(string $token, User $user): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->jwt->factory()->getTTL() * 60,
            'user' => $user->only(['id', 'name', 'email', 'is_active', 'role_code']),
        ];
    }
}
