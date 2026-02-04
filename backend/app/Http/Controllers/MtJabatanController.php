<?php

namespace App\Http\Controllers;

use App\Models\MtJabatan;

class MtJabatanController extends Controller {
    public function getJabatan() {
        return response()->json(MtJabatan::all());
    }

}