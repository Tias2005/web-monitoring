<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MtRoleController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MtJabatanController;
use App\Http\Controllers\MtDivisiController;
use App\Http\Controllers\MtPresensiController;
use App\Http\Controllers\MtPengajuanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MtJamKerjaController;
use App\Http\Controllers\MtHariKerjaController;
use App\Http\Controllers\MtHariLiburController;
use App\Http\Controllers\MtNotifikasiController;
use App\Http\Controllers\MtJatahCutiController;
use App\Http\Controllers\MtLokasiPresensiController;

Route::options('{any}', function (Request $request) {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', '*');
})->where('any', '.*');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-mobile', [AuthController::class, 'loginMobile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::match(['post', 'put'], '/user/update/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/user/register-face', [AuthController::class, 'registerFace']);
});

Route::get('/dashboard-stats', [DashboardController::class, 'getStats']);
Route::get('/user-stats/{id_user}', [DashboardController::class, 'getUserStats']);

Route::get('/roles', [MtRoleController::class, 'index']);
Route::post('/roles', [MtRoleController::class, 'store']);

Route::prefix('karyawan')->group(function () {
    Route::get('/', [KaryawanController::class, 'index']);
    Route::get('/export', [KaryawanController::class, 'export']);
    Route::get('/{id}', [KaryawanController::class, 'show']);
    Route::post('/', [KaryawanController::class, 'store']);
    Route::put('/{id}', [KaryawanController::class, 'update']);
    Route::delete('/{id}', [KaryawanController::class, 'destroy']);
});

Route::get('/jabatan', [MtJabatanController::class, 'getJabatan']);
Route::get('/divisi', [MtDivisiController::class, 'getDivisi']);

Route::get('/presensi', [MtPresensiController::class, 'index']);
Route::get('/presensi/today/{id_user}', [MtPresensiController::class, 'getTodayStatus']);
Route::get('/presensi/calendar/{id_user}', [MtPresensiController::class, 'getCalendarEvents']);

Route::prefix('pengajuan')->group(function () {
    Route::get('/', [MtPengajuanController::class, 'index']);
    Route::get('/{id}', [MtPengajuanController::class, 'show']);
    Route::get('/download/{id}', [MtPengajuanController::class, 'download']);
    Route::post('/store', [MtPengajuanController::class, 'store']);
});

Route::get('/laporan', [LaporanController::class, 'index']);
Route::get('/laporan/export', [LaporanController::class, 'exportExcel']);

Route::get('/jam-kerja', [MtJamKerjaController::class, 'index']);
Route::put('/jam-kerja/{id}', [MtJamKerjaController::class, 'update']);

Route::get('/hari-kerja', [MtHariKerjaController::class, 'index']);
Route::put('/hari-kerja/{id}', [MtHariKerjaController::class, 'update']);

Route::get('/hari-libur', [MtHariLiburController::class, 'index']);
Route::post('/hari-libur', [MtHariLiburController::class, 'store']);
Route::delete('/hari-libur/{id}', [MtHariLiburController::class, 'destroy']);

Route::get('/notifications/{id_user}', [MtNotifikasiController::class, 'getByUser']);
Route::put('/notifications/read/{id}', [MtNotifikasiController::class, 'markAsRead']);

Route::prefix('jatah-cuti')->group(function () {
    Route::get('/global', [MtJatahCutiController::class, 'getGlobalSetting']);
    Route::post('/global/update', [MtJatahCutiController::class, 'updateGlobal']);
    Route::get('/karyawan/{id_user}', [MtJatahCutiController::class, 'getSisaCutiKaryawan']);
});

Route::get('/lokasi-presensi', [MtLokasiPresensiController::class, 'index']);
Route::post('/presensi/store', [MtPresensiController::class, 'store']);
Route::post('/lokasi-presensi/update', [MtLokasiPresensiController::class, 'update']);