<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCrmAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessCrm()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account does not have access to the CRM.',
            ]);
        }

        return $next($request);
    }
}
