<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Auth\IssueTokenRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends ApiController
{
    public function store(IssueTokenRequest $request)
    {
        $email = (string) $request->string('email');
        $password = (string) $request->string('password');
        $deviceName = (string) $request->string('device_name');

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return $this->error('Invalid credentials.', 422, [
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => UserResource::make($user->load('organizations')),
        ], status: 201);
    }

    public function destroy(Request $request)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return $this->success([
            'revoked' => (bool) $token,
        ]);
    }
}
