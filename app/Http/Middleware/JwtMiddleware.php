<?php

namespace App\Http\Middleware;

use App\Http\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $jwt = JwtService::cekToken($token);
        if ($jwt->getStatusCode() != 200) {
            return $jwt;
        }
        $res = json_decode($jwt->getContent());
        $request->attributes->set('user_id', $res->user_id);
        
        return $next($request);
    }
}
