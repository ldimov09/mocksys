<?php

namespace App\Http\Controllers;

use App\Models\FiscalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiscalRecordController extends Controller
{
    public function index()
    {
        $fiscalRecords = FiscalRecord::query()
            ->leftJoin('companies as direct_company', 'fiscal_records.company_id', '=', 'direct_company.id')
            ->leftJoin('companies as business_company', 'fiscal_records.business_id', '=', 'business_company.account_id')
            ->leftJoin('transactions', 'fiscal_records.transaction_id', '=', 'transactions.id')
            ->leftJoin('users as business', 'fiscal_records.business_id', '=', 'business.id')
            ->select([
                'fiscal_records.*',
                DB::raw('COALESCE(direct_company.id, business_company.id) as company_id'),
                DB::raw('COALESCE(direct_company.name, business_company.name) as company_name'),
                DB::raw('business.name as business_name'),
            ])
            ->orderByDesc('fiscal_records.created_at')
            ->get();

        return view('admin.fiscal-records.index', compact('fiscalRecords'));
    }
}
