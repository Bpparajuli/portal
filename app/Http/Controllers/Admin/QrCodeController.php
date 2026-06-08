<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class QrCodeController extends Controller
{
    public function index()
    {
        return view('admin.qr-js');
    }
}
