<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MtRoleController;
use App\Http\Controllers\KaryawanController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/roles', [MtRoleController::class, 'index']);
Route::post('/roles', [MtRoleController::class, 'store']);

Route::prefix('karyawan')->group(function () {
    Route::get('/', [KaryawanController::class, 'index']);          // Ambil semua karyawan
    Route::get('/export', [KaryawanController::class, 'export']);   // Export Excel
    Route::get('/{id}', [KaryawanController::class, 'show']);       // Detail 1 karyawan
    Route::post('/', [KaryawanController::class, 'store']);         // Tambah karyawan
    Route::put('/{id}', [KaryawanController::class, 'update']);     // Edit karyawan
    Route::delete('/{id}', [KaryawanController::class, 'destroy']);  // Hapus karyawan
});