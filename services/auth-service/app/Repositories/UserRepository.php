<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class UserRepository
{
    public function findActiveByEmail(string $email): ?User
    {
        return User::with('role')->where('email', $email)->where('is_active', true)->first();
    }

    public function findOrFail(int $id): User
    {
        return User::with('role')->whereHas('role', fn ($query) => $query->where('code', '!=', Role::ADMIN))->findOrFail($id);
    }

    public function paginate(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return User::with('role')
            ->whereHas('role', fn ($query) => $query->where('code', '!=', Role::ADMIN))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(array $attributes): User
    {
        return User::create($attributes)->load('role');
    }

    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);
        return $user->fresh('role');
    }

    public function roleIdByCode(string $code): int
    {
        return (int) Role::where('code', $code)->valueOrFail('id');
    }
}
