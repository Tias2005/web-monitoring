<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_karyawan' => DB::table('users')->count(),
            'hadir_hari_ini' => 18,
            'terlambat' => 3
        ]);
    }
}
