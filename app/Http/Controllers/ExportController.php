<?php

namespace App\Http\Controllers;

use App\Exports\AssetsExport;

class ExportController extends Controller
{
    public function assets()
    {
        $export = new AssetsExport();
        return $export->download();
    }
}