<?php

namespace App\Http\Controllers;

use App\Models\FiscalRecord;
use Illuminate\Http\Request;

class FiscalRecordController extends Controller
{
    public function index()
    {
        $fiscalRecords = FiscalRecord::with(['transaction', 'business', 'company'])->orderByDesc('created_at')->get();
        return view('admin.fiscal-records.index', compact('fiscalRecords'));
    }
}
