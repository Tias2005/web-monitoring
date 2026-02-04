<?php

namespace App\Http\Controllers;

use App\Models\MtDivisi;

class MtDivisiController extends Controller {
    public function getDivisi() {
        return response()->json(MtDivisi::all());
    }
}