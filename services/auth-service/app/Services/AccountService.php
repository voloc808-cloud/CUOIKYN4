<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

final class AccountService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function paginate(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($search, $perPage);
    }

    public function find(int $id): User
    {
        return $this->users->findOrFail($id);
    }

    public function create(array $data): User
    {
        $data['role_id'] = $this->users->roleIdByCode($data['role']);
        $data['password'] = Hash::make($data['password']);
        unset($data['role']);

        return $this->users->create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->users->findOrFail($id);
        if (isset($data['role'])) {
            $data['role_id'] = $this->users->roleIdByCode($data['role']);
            unset($data['role']);
        }
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->users->update($user, $data);
    }

    public function changeStatus(int $id, bool $isActive, int $actorId): User
    {
        if ($id === $actorId && !$isActive) {
            throw new BusinessException('Bạn không thể tự khóa tài khoản đang đăng nhập', 'CANNOT_LOCK_SELF');
        }

        return $this->users->update($this->users->findOrFail($id), ['is_active' => $isActive]);
    }

    public function delete(int $id, int $actorId): void
    {
        if ($id === $actorId) {
            throw new BusinessException('Bạn không thể tự xóa tài khoản đang đăng nhập', 'CANNOT_DELETE_SELF');
        }

        $this->users->findOrFail($id)->delete();
    }
}
