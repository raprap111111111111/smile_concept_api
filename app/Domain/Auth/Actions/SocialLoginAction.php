<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\SocialLoginDTO;
use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class SocialLoginAction
{
    public function execute(SocialLoginDTO $dto): User
    {
        $socialUser = $this->verifyTokenWithProvider($dto->provider, $dto->token);

        $socialAccount = SocialAccount::where('provider', $dto->provider)
            ->where('provider_id', $socialUser['id'])
            ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        }

        $user = User::where('email', $socialUser['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser['name'],
                'email' => $socialUser['email'],
                'password' => Hash::make(Str::random(24)),
            ]);
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $dto->provider,
            'provider_id' => $socialUser['id'],
        ]);

        return $user;
    }

    private function verifyTokenWithProvider(string $provider, string $token): array
    {
        if ($provider === 'google') {
            $response = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$token}");
            
            if ($response->failed() || isset($response['error'])) {
                throw new Exception("Invalid Google Token.");
            }

            return [
                'id' => $response['sub'],
                'name' => $response['name'],
                'email' => $response['email'],
            ];
        }

        if ($provider === 'facebook') {
            $response = Http::get("https://graph.facebook.com/me", [
                'access_token' => $token,
                'fields' => 'id,name,email',
            ]);

            if ($response->failed() || isset($response['error'])) {
                throw new Exception("Invalid Facebook Token.");
            }

            return [
                'id' => $response['id'],
                'name' => $response['name'],
                'email' => $response['email'] ?? $response['id'] . '@facebook.com',
            ];
        }

        throw new Exception("Unsupported social login provider.");
    }
}
