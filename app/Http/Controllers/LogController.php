<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::with('user')->orderBy('created_at', 'desc')->get();

        return view('admin.logs.index', compact('logs'));
    }
}
