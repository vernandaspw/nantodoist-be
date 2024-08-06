<?php

namespace App\Http;

use App\Models\UserToken;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    public static function jwtCreateToken($user_id)
    {
        $payload = [
            'sub' => $user_id,
            'iat' => time(),
            'exp' => time() + 86400, //Expired time 1 day
        ];

        return JWT::encode($payload, env('JWT_SECRET'), env('JWT_ALGO'));
    }

    public static function jwtCreateTokenRefresh($user_id)
    {
        $payload = [
            'sub' => $user_id,
            'iat' => time(),
            'exp' => time() + 86400, //Expired time 1 day
        ];

        return JWT::encode($payload, env('JWT_SECRET_REFRESH'), env('JWT_ALGO'));
    }

    public static function cekToken($token)
    {
        // cek input token
        if (!$token) {
            return response()->json([
                'msg' => 'token wajib di isi',
            ], 401);
        }

        // cek verify token
        $jwtVerify = JwtService::jwtVerifyToken($token);

        if ($jwtVerify->getStatusCode() != 200) {
            return $jwtVerify;
        }

        // cek token pada userToken
        $userToken = UserToken::where('token', $token)->first();
        if (!$userToken) {
            return response()->json([
                'msg' => 'token invalid!',
            ], 401);
        }

        return response()->json([
            'msg' => 'token valid',
            'user_id' => $userToken->user_id,
        ], 200);
    }

    public static function jwtVerifyToken($token)
    {
        // $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'error' => 'Token not provided.',
            ], 401);
        }
        $key = env('JWT_SECRET');
        if (!$key) {
            return response()->json([
                'error' => 'Key invalid!',
            ], 401);
        }

        try {
            return response()->json([
                'msg' => 'success',
                'data' => JWT::decode($token, new Key($key, 'HS256')),
            ], 200);
        } catch (ExpiredException $e) {
            return response()->json([
                'msg' => 'Provided token is expired.',
            ], 403);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'msg' => 'Wrong signature token or secret key.',
            ], 401);
        } catch (BeforeValidException $e) {
            return response()->json([
                'msg' => 'Token is not yet valid',
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Invalid token',
            ], 401);
        }
    }

    public static function jwtVerifyTokenRefresh($token)
    {
        // $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'error' => 'Token not provided.',
            ], 401);
        }
        $key = env('JWT_SECRET_REFRESH');
        if (!$key) {
            return response()->json([
                'error' => 'Key invalid!',
            ], 401);
        }

        try {
            return response()->json([
                'msg' => 'success',
                'data' => JWT::decode($token, new Key($key, 'HS256')),
            ], 200);
        } catch (ExpiredException $e) {
            return response()->json([
                'msg' => 'Provided token is expired.',
            ], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'msg' => 'Wrong signature token or secret key.',
            ], 401);
        } catch (BeforeValidException $e) {
            return response()->json([
                'msg' => 'Token is not yet valid',
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Invalid token',
            ], 401);
        }
    }
}
