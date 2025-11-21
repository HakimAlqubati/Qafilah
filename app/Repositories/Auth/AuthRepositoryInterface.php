<?php

namespace App\Repositories\Auth;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function attemptLogin(string $email, string $password): ?User;

    public function logout(User $user, ?string $tokenId = null): void;
}
