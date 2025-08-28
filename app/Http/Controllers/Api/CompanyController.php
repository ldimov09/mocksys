<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CompanyDigitHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Company;

class CompanyController extends Controller
{
    public function __construct(protected CompanyRepository $companyRepo) {}

    public function show()
    {
        $company = $this->companyRepo->findByUserId(Auth::id());

        if (!$company) {
            return response()->json(['message' => __('t.company.not_found')], 404);
        }

        return response()->json($company);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')],
            'manager_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'legal_form' => ['required', Rule::in(['ad', 'ead', 'eood', 'et', 'ood'])],
        ]);

        if ($this->companyRepo->findByUserId(Auth::id())) {
            return response()->json(['message' => __('t.company.already_exists')], 409);
        }

        // Create base record without number
        $company = Company::create([
            'account_id' => Auth::id(),
            'manager_name' => $request->manager_name,
            'name' => $request->name,
            'address' => $request->address,
            'legal_form' => $request->legal_form,
            'number' => 'placeholder' // Temporary number
        ]);

        // Generate EIK number: pad to 8 digits + 1 check digit
        $baseNumber = str_pad($company->id, 8, '0', STR_PAD_LEFT);
        $checkDigit = CompanyDigitHelper::calculateEIKCheckDigit($baseNumber);
        $fullNumber = $baseNumber . $checkDigit;

        // Save it
        $company->number = $fullNumber;
        $company->save();

        return response()->json($company, 201);
    }

    public function update(Request $request)
    {
        $company = $this->companyRepo->findByUserId(Auth::id());

        if (!$company) {
            return response()->json(['message' => __('t.company.not_found')], 404);
        }

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company->id)],
            'manager_name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'string'],
            'legal_form' => ['sometimes', Rule::in(['ad', 'ead', 'eood', 'et', 'ood'])],
        ]);

        $company->update($request->only(['name', 'manager_name', 'address', 'legal_form']));

        return response()->json($company);
    }

    public function destroy()
    {
        $company = $this->companyRepo->findByUserId(Auth::id());

        if (!$company) {
            return response()->json(['message' => __('t.company.not_found')], 404);
        }

        $this->companyRepo->delete($company->id);

        return response()->json(['message' => __('t.company.deleted')]);
    }
}
