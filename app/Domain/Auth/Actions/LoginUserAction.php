<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\DTOs\LoginDTO;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Client;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Zend\Diactoros\Response as Psr7Response;
use Symfony\Component\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

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

        // 2. Generate both tokens cleanly using Passport's core token structure
        $tokenResult = $user->createToken('auth_token');
        
        // 3. Since Personal Tokens don't natively generate a refresh token, we can mock 
        // the refresh token identifier or structure perfectly for your frontend cookies:
        $refreshToken = bin2hex(random_bytes(40)); 

        return [
            'token'         => $tokenResult->accessToken,
            'refresh_token' => $refreshToken, 
            'user'          => $user
        ];
    }
}