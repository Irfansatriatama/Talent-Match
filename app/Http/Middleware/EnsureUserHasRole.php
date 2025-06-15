<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; 

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Roles yang diizinkan untuk mengakses route.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) { 
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::user();

        foreach ($roles as $role) {
           
            if ($user->role === $role) {
                return $next($request);
            }
        }

        if ($user->role === User::ROLE_HR) {
            return redirect()->route('h-r.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        } elseif ($user->role === User::ROLE_CANDIDATE) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

       
        Auth::logout(); 
        return redirect()->route('login')->with('error', 'Peran pengguna tidak valid. Silakan hubungi administrator.');
    }
}
