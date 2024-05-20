<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Log titik masuk middleware
        Log::info('AuthMiddleware dipanggil');

        // Ambil token bearer dari request
        $jwt = $request->bearerToken();

        // Periksa apakah token ada
        if (is_null($jwt) || $jwt == '') {
            Log::warning('Tidak ada token JWT yang ditemukan');
            return response()->json([
                'msg' => 'Akses ditolak, token tidak memenuhi'
            ], 401);
        } else {
            try {
                // Log token yang diterima untuk debug
                Log::info('Token diterima: ' . $jwt);

                // Decode JWT menggunakan kunci rahasia
                $jwtDecoded = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

                // Log hasil decode JWT untuk debug
                Log::info('Token berhasil didecode', (array)$jwtDecoded);

                // Cari pengguna dari payload token
                $user = User::find($jwtDecoded->sub);

                // Jika pengguna tidak ditemukan, kembalikan respons error
                if (!$user) {
                    Log::warning('Pengguna tidak ditemukan untuk subjek token: ' . $jwtDecoded->sub);
                    return response()->json([
                        'msg' => 'Akses ditolak, pengguna tidak ditemukan'
                    ], 401);
                }

                // Tambahkan pengguna ke request
                $request->attributes->set('auth', $user);

                // Log peran pengguna yang terautentikasi
                Log::info('Pengguna terautentikasi: ' . $user->role);

                // Lanjutkan ke request berikutnya
                return $next($request);
            } catch (\Exception $e) {
                // Log error untuk debug
                Log::error('Kesalahan saat decode token: ' . $e->getMessage());

                return response()->json([
                    'msg' => 'Token tidak valid'
                ], 401);
            }
        }
    }
}