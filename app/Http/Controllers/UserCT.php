<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCT extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }
    
        $validated = $validator->validated();
    
        if (Auth::attempt($validated)) {
            $user = Auth::user(); // Ambil pengguna yang terautentikasi
    
            $payload = [
                'sub' => $user->id, // Pastikan ini adalah ID pengguna
                'name' => 'Administrator',
                'role' => 'admin',
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->timestamp + 3600,
            ];
    
            $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');
    
            return response()->json([
                'msg' => 'token berhasil dibuat',
                'data' => 'Bearer ' . $token
            ], 200);
        } else {
            return response()->json([
                'msg' => 'Email atau Password salah'
            ], 422);
        }
    }
}