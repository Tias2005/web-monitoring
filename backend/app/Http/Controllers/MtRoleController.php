<?php

namespace App\Http\Controllers;

use App\Models\MtRole;
use Illuminate\Http\Request;

class MtRoleController extends Controller
{
    public function index()
    {
        $roles = MtRole::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_role' => 'required|unique:mt_role,nama_role'
        ]);

        $role = MtRole::create([
            'nama_role' => $request->nama_role
        ]);

        return response()->json([
            'message' => 'Role berhasil ditambahkan',
            'data' => $role
        ], 201);
    }
}