<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class AuthorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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
        $userId = Auth::guard('dev-api')->user()->id;
        $game = Game::where('created_by' , $userId)->exists();
        if (Auth::guard('dev-api')->check()) {
            if(!$game)
            {
                return $next($request);
            }

        }

        return response()->json([
            'status' => 'forbidden',
            'message' => 'you are not the game author.'
        ], 403);
    }

}
