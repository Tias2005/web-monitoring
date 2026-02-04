<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MtRoleController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MtJabatanController;
use App\Http\Controllers\MtDivisiController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

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