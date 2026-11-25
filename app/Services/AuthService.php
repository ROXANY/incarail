<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos.',
            ]);
        }

        if (! $user->is_active) {
            throw new ApiException(403, 'forbidden', 'Tu usuario no esta activo.');
        }

        return $user;
    }
}
