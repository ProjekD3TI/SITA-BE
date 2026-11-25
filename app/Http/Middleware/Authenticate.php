<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            if ($request->is('api/mahasiswa/*')) {
                return route('login.mahasiswa');
            } elseif ($request->is('api/dosen/*')) {
                return route('login.dosen');
            } else {
                return route('login.dosen');
            }
        }

        return null;
    }

    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('sanctum')->check()) {
            return $this->auth->shouldUse('sanctum');
        }

        $this->unauthenticated($request, $guards);
    }


}