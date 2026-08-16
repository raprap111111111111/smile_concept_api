<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\LoginDTO;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Events\Failed;  
use Illuminate\Auth\Events\Login;    
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Client;

class LoginUserAction
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(LoginDTO $dto)
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            // ✅ FIRE FAILED EVENT — triggers LogFailedLogin listener
            event(new Failed('api', $user, ['email' => $dto->email]));

            throw ValidationException::withMessages([
                'email' => ['Sorry, email and password do not match.'],
            ]);
        }

        // 1. Fetch the OAuth Client
        $client = Client::all()->first(function ($c) {
            return ($c->password_client ?? false) == true || ($c->password_access_client ?? false) == true;
        }) ?? Client::first();

        if (! $client) {
            throw ValidationException::withMessages([
                'email' => ['OAuth Client missing. Please run php artisan passport:client --password'],
            ]);
        }

        // 2. Generate tokens
        $tokenResult = $user->createToken('auth_token');
        $refreshToken = bin2hex(random_bytes(40));

        // ✅ FIRE LOGIN EVENT — triggers LogSuccessfulLogin listener
        event(new Login('api', $user, false));

        return [
            'token'         => $tokenResult->accessToken,
            'refresh_token' => $refreshToken,
            'user'          => $user,
        ];
    }
}