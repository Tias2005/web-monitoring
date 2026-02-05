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

    public function updateProfile(Request $request, $id)
    {
        $user = MtUser::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_user'  => 'required|string|max:255',
            'email_user' => 'required|email|unique:mt_user,email_user,' . $id . ',id_user',
            'password_before' => 'nullable',
            'new_password'    => 'nullable|confirmed',
        ]);

        $user->nama_user = $request->nama_user;
        $user->email_user = $request->email_user;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->password_before, $user->password_user)) {
                return response()->json(['message' => 'Password lama tidak sesuai'], 422);
            }
            $user->password_user = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'id_user'   => $user->id_user,
                'name_user' => $user->nama_user,
                'email_user'=> $user->email_user,
            ]
        ]);
    }
}
