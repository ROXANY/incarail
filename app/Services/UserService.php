<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function create(array $data): User
    {
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            $user->syncRoles([$data['role']]);

            return $user;
        });

        return $user;
    }

    public function update(User $user, array $data, User $currentUser): User
    {
        $isActive = $data['is_active'] ?? $user->is_active;

        if ($user->id === $currentUser->id && ! $isActive) {
            throw ValidationException::withMessages([
                'is_active' => 'No puedes desactivar tu propio usuario.',
            ]);
        }

        DB::transaction(function () use ($user, $data, $isActive) {
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->is_active = $isActive;

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();
            $user->syncRoles([$data['role']]);

            if (! $isActive) {
                $user->tokens()->update(['revoked' => true]);
            }
        });

        return $user;
    }
}
