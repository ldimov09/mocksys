<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index()
    {
        $companies = Company::with('account')->get();
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Display a form for creating a company
     */
    public function create()
    {
        $businessUsers = User::where('role', 'business')->get();
        return view('admin.companies.create', compact('businessUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'manager_name' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:companies,name',
            'number' => 'required|digits:8|unique:companies,number',
            'address' => 'required|string|max:255',
            'legal_form' => 'required|string|max:100',
            'account_id' => 'required|exists:users,id',
        ]);

        // Apply the check-digit algorithm
        $eik8 = $validated['number'];
        $validated['number'] = $eik8 . $this->calculateEIKCheckDigit($eik8);

        if (Company::where('number', '=', $validated['number'])->first()) {
            return redirect()->back()
                ->with('error', 'Company with this number already exists!');
        }

        $company = Company::create([
            'manager_name' => $validated['manager_name'],
            'name' => $validated['name'],
            'number' => $validated['number'],
            'address' => $validated['address'],
            'legal_form' => $validated['legal_form'],
            'account_id' => $validated['account_id'],
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company created successfully!');
    }

    private function calculateEIKCheckDigit(string $eik8): int
    {
        $digits = str_split($eik8);

        $coeffs1 = [1, 2, 3, 4, 5, 6, 7, 8];
        $sum1 = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum1 += $digits[$i] * $coeffs1[$i];
        }

        $remainder = $sum1 % 11;
        if ($remainder < 10) {
            return $remainder;
        }

        $coeffs2 = [3, 4, 5, 6, 7, 8, 9, 10];
        $sum2 = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum2 += $digits[$i] * $coeffs2[$i];
        }

        $remainder2 = $sum2 % 11;
        return $remainder2 < 10 ? $remainder2 : 0;
    }

    public function edit(Company $company)
    {
        $businessUsers = User::where('role', 'business')->get();

        return view('admin.companies.edit', compact('company', 'businessUsers'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'manager_name' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'number' => 'required|digits:9|unique:companies,number,' . $company->id,
            'address' => 'required|string|max:255',
            'legal_form' => 'required|string|max:100',
            'account_id' => 'required|exists:users,id',
        ]);

        $company->update($validated);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Company updated successfully!');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
