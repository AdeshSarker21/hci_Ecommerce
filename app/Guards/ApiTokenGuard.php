<?php

namespace App\Guards;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class ApiTokenGuard implements Guard
{
    protected $request;
    protected $provider;
    protected $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
    }

    public function user(): ?User
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->request->bearerToken();

        if (!$token) {
            return null;
        }

        $apiToken = ApiToken::where('token', hash('sha256', $token))
            ->with('user')
            ->first();

        if (!$apiToken || $apiToken->isExpired()) {
            return null;
        }

        if (!$apiToken->user || !$apiToken->user->is_active) {
            return null;
        }

        $apiToken->markUsed();

        return $this->user = $apiToken->user;
    }

    public function id(): ?string
    {
        return $this->user()?->getKey();
    }

    public function check(): bool
    {
        return !is_null($this->user());
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function setUser(\Illuminate\Contracts\Auth\Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    public function logout(): void
    {
        $this->user = null;
    }
}
