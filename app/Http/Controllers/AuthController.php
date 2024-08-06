<?php

namespace App\Http\Controllers;

use App\Http\JwtService;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function daftar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:3',
            'ulangi_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::create([
                'nama' => $req->nama,
                'email' => $req->email,
                'password' => $req->password,
            ]);
            return response()->json([
                'msg' => 'success',
                'data' => $user,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $req->email)->first();
            if (!$user) {
                return response()->json([
                    'msg' => 'email tidak ditemukan',
                ], 400);
            }
            $cekPass = Hash::check($req->password, $user->password);
            if (!$cekPass) {
                return response()->json([
                    'msg' => 'password salah',
                ], 400);
            }

            $token = JwtService::jwtCreateToken($user->id);
            $tokenRefresh = JwtService::jwtCreateTokenRefresh($user->id);

            $device = $req->header('User-Agent');

            $userToken = UserToken::where('user_id', $user->id)->where('device', $device)->first();
            if ($userToken) {
                $userToken->token = $token;
                $userToken->tokenRefresh = $tokenRefresh;
                $userToken->save();
            } else {
                UserToken::create([
                    'user_id' => $user->id,
                    'device' => $device,
                    'token' => $token,
                    'tokenRefresh' => $tokenRefresh,
                ]);
            }

            return response()->json([
                'msg' => 'success',
                'user_id' => $user->id,
                'token' => $token,
                'tokenRefresh' => $tokenRefresh,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $req)
    {
        try {
            $jwt = JwtService::cekToken($req->bearerToken());
            if ($jwt->getStatusCode() != 200) {
                return $jwt;
            }
            $res = json_decode($jwt->getContent());
            $user_id = $res->user_id;
            // $user_id = $req->attributes->get('user_id');
            $user = User::where('id', $user_id)->first();

            return response()->json([
                'msg' => 'success',
                'data' => $user,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function refreshToken(Request $req)
    {
        try {
            $input_token = $req->tokenRefresh;
            if (!$input_token) {
                return response()->json([
                    'msg' => 'tokenRefresh wajib di isi!',
                ], 401);
            }
            $jwtVerify = JwtService::jwtVerifyTokenRefresh($input_token);
            if ($jwtVerify->getStatusCode() != 200) {
                return $jwtVerify;
            }
            $response = json_decode($jwtVerify->getContent());

            $userToken = UserToken::where('tokenRefresh', $input_token)->first();
            if (!$userToken) {
                return response()->json([
                    'msg' => 'refreshToken invalid!',
                ], 401);
            }

            $token = JwtService::jwtCreateToken($userToken->user_id);
            $tokenRefresh = JwtService::jwtCreateTokenRefresh($userToken->user_id);

            $userToken->token = $token;
            $userToken->tokenRefresh = $tokenRefresh;
            $userToken->save();

            return response()->json([
                'msg' => 'success',
                'user_id' => $userToken->user_id,
                'token' => $token,
                'tokenRefresh' => $tokenRefresh,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $req)
    {
        try {
            $token = $req->bearerToken();
            $userToken = UserToken::where('token', $token)->first();
            $userToken->delete();

            return response()->json([
                'msg' => 'success logout',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function logoutDevice($userTokenId)
    {

    }

    public function logoutAllDevice()
    {

    }

}
