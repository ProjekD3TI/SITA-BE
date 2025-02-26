<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\JabatanDosen;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatanDosen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $jabatan)
    {
        $hasRole = JabatanDosen::where('dosen_id', auth()->id())
            ->whereHas('jabatan', function($query) use ($jabatan) {
                $query->where('name', $jabatan);
            })
            ->where('is_active', true)
            ->exists();

        if (!$hasRole) {
            return response()->json([
                'status' => false,
                'message' => 'User Unauthorized.'
            ], 403);
        }

        return $next($request);
    }
}
