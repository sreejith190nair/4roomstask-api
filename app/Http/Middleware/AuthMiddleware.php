<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if token is provided in the Authorization header
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (TokenExpiredException $e) {
            // Token has expired
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            // Token is invalid
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            // Token is missing or there's a problem parsing the token
            return response()->json(['error' => 'Token is missing'], 400);
        }catch (Exception $e){
            return response()->json(['error' => 'Something went wrong!'], 500);
        }

        // If everything is fine, proceed with the request
        return $next($request);
    }
}
