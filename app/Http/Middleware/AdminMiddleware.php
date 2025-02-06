<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil token dari header Authorization
        $token = $request->bearerToken();

        // Log token yang diterima untuk debug
        Log::info('Token yang diterima:', ['Token' => $token]);

        // Jika token tidak ditemukan, kirim respons error
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak disertakan.'
            ], 400);
        }

        // Lakukan pengecekan apakah token valid dan sesuai dengan guard 'admin-api'
        if (Auth::guard('admin-api')->check()) {
            return $next($request);
        }

        return response()->json([
            'status' => 'forbidden',
            'message' => 'You are not the administrator'
        ], 403);
    }
}
