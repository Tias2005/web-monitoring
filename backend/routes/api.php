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


Route::post('/login', [AuthController::class, 'login']);
Route::put('/user/update/{id}', [AuthController::class, 'updateProfile']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/dashboard-stats', [DashboardController::class, 'getStats']);

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

Route::prefix('pengajuan')->group(function () {
    Route::get('/', [MtPengajuanController::class, 'index']);
    Route::get('/{id}', [MtPengajuanController::class, 'show']);
    Route::get('/download/{id}', [MtPengajuanController::class, 'download']);
});

Route::get('/laporan', [LaporanController::class, 'index']);
Route::get('/laporan/export', [LaporanController::class, 'exportExcel']);
