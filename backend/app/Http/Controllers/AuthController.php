<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\MtUser;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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

        $admin = MtUser::find($user->id_user);

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user' => [
                'id_user'   => $admin->id_user,
                'name_user' => $admin->nama_user,
                'email_user'=> $admin->email_user,
                'role'      => $user->nama_role,
            ]
        ]);
    }

    public function loginMobile(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = MtUser::with(['jabatan', 'divisi'])
            ->where('email_user', $request->email)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar'], 401);
        }

        if ($user->id_role != 2) {
            return response()->json(['message' => 'Akses ditolak. Khusus aplikasi Karyawan.'], 403);
        }

        if (is_null($user->password_user)) {
            $user->password_user = Hash::make($request->password);
            $user->save();
            $message = 'Password berhasil dibuat dan login berhasil!';
        } else {
            if (!Hash::check($request->password, $user->password_user)) {
                return response()->json(['message' => 'Password salah'], 401);
            }
            $message = 'Login berhasil';
        }

        $token = $user->createToken('mobile_token')->plainTextToken;
        $statusTeks = ($user->status_user == 1) ? "Aktif" : "Tidak Aktif";
        $tglJoin = $user->tanggal_bergabung ? \Carbon\Carbon::parse($user->tanggal_bergabung)->format('Y-m-d') : '-';

        return response()->json([
            'message' => $message,
            'token'   => $token, 
            'user' => [
                'id_user'          => (string) $user->id_user,
                'nama_user'        => $user->nama_user,
                'email_user'       => $user->email_user,
                'foto_profil'      => $user->foto_profil,
                'embedding_vector' => $user->embedding_vector,
                'jabatan'          => $user->jabatan->nama_jabatan ?? '-',
                'divisi'           => $user->divisi->nama_divisi ?? '-',
                'tanggal_bergabung'=> $tglJoin,            
                'no_telepon'       => (string) $user->no_telepon,
                'alamat'           => $user->alamat,
                'latitude_rumah'   => $user->latitude_rumah,
                'longitude_rumah'  => $user->longitude_rumah,
                'status_user'      => $statusTeks,
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
            'no_telepon' => 'nullable|string',
            'alamat'     => 'nullable|string',
            'latitude_rumah' => 'nullable|numeric',
            'longitude_rumah' => 'nullable|numeric',
            'password_before' => 'nullable',
            'new_password'    => 'nullable|min:6',
        ]);

        $passwordChanged = false;
        $user->nama_user = $request->nama_user;
        $user->email_user = $request->email_user;
        $user->no_telepon = $request->no_telepon;
        $user->alamat = $request->alamat;
        $user->latitude_rumah = $request->latitude_rumah;
        $user->longitude_rumah = $request->longitude_rumah;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->password_before, $user->password_user)) {
                return response()->json(['message' => 'Password lama tidak sesuai'], 422);
            }
            $user->password_user = Hash::make($request->new_password);
            $passwordChanged = true;
        }

        $user->save();

        $user->load(['jabatan', 'divisi']);

        $statusTeks = ($user->status_user == 1) ? "Aktif" : "Tidak Aktif";
        $tglJoin = $user->tanggal_bergabung ? \Carbon\Carbon::parse($user->tanggal_bergabung)->format('Y-m-d') : '-';


        $userData = [
            'id_user'           => (string) $user->id_user,
            'nama_user'         => $user->nama_user,
            'email_user'        => $user->email_user,
            'foto_profil'       => $user->foto_profil,
            'jabatan'           => $user->jabatan->nama_jabatan ?? '-',
            'divisi'            => $user->divisi->nama_divisi ?? '-',
            'tanggal_bergabung' => $tglJoin,
            'no_telepon'        => (string) $user->no_telepon,
            'alamat'            => $user->alamat,
            'latitude_rumah'    => $user->latitude_rumah,
            'longitude_rumah'   => $user->longitude_rumah,
            'status_user'       => $statusTeks,
        ];

        return response()->json([
            'message' => $passwordChanged 
                ? 'Password berhasil diubah. Silakan login ulang.' 
                : 'Profil berhasil diperbarui',
            'password_changed' => $passwordChanged,
            'user' => $userData
        ]);
    }

    public function registerFace(Request $request)
    {
        $request->validate([
            'id_user'   => 'required', 
            'embedding' => 'required|string',
            'foto'      => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user = MtUser::find($request->id_user);

            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            if ($request->hasFile('foto')) {
                if ($user->foto_profil) {
                    Storage::disk('public')->delete($user->foto_profil);
                }
                $path = $request->file('foto')->store('profile_faces', 'public');
                $user->foto_profil = $path;
            }

            $user->embedding_vector = DB::raw("'" . $request->embedding . "'::double precision[]");        
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Wajah berhasil didaftarkan.',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'fcm_token' => 'required'
        ]);

        $user = \App\Models\MtUser::find($request->id_user);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['message' => 'Token tersimpan']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email_user' => 'required|email']);
        $user = MtUser::where('email_user', $request->email_user)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar'], 404);
        }

        $otp = rand(100000, 999999);

        DB::table('password_reset_otps')->where('email', $request->email_user)->delete();
        DB::table('password_reset_otps')->insert([
            'email' => $request->email_user,
            'otp' => $otp, 
            'created_at' => Carbon::now()
        ]);

        try {
            Mail::raw("Kode OTP Anda untuk reset password adalah: $otp. Kode ini berlaku selama 15 menit.", function ($message) use ($user) {
                $message->to($user->email_user)
                        ->subject('Reset Password OTP - Monitoring Admin');
            });
            return response()->json(['message' => 'Kode OTP telah dikirim ke email Anda.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email_user' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $reset = DB::table('password_reset_otps')
            ->where('email', $request->email_user)
            ->where('otp', $request->otp)
            ->where('created_at', '>', Carbon::now()->subMinutes(15))
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Kode OTP salah atau sudah kedaluwarsa'], 422);
        }

        return response()->json(['message' => 'OTP valid, silakan reset password Anda.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email_user' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $check = DB::table('password_reset_otps')
            ->where('email', $request->email_user)
            ->where('otp', $request->otp)
            ->first();

        if (!$check) {
            return response()->json(['message' => 'Permintaan tidak valid'], 400);
        }

        $user = MtUser::where('email_user', $request->email_user)->first();
        $user->password_user = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_otps')->where('email', $request->email_user)->delete();

        return response()->json(['message' => 'Password berhasil diubah. Silakan login kembali.']);
    }

}
