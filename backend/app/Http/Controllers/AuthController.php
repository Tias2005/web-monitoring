<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\MtUser;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email_user'    => 'required|email',
            'password_user' => 'required'
        ]); 

        $user = DB::table('mt_user')
            ->join('mt_role', 'mt_user.id_role', '=', 'mt_role.id_role')
            ->where('mt_user.email_user', $request->email_user)
            ->select('mt_user.*', 'mt_role.nama_role')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar'], 401);
        }

        if ($user->id_role != 1) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan Admin.'], 403);
        }

        if (!Hash::check($request->password_user, $user->password_user)) {
            return response()->json(['message' => 'Password salah'], 401);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'data' => [
                'id_user'   => $user->id_user,
                'name_user' => $user->nama_user,
                'email_user'=> $user->email_user,
                'role'      => $user->nama_role, 
            ]
        ]);
    }
}
