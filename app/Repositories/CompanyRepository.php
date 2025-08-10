<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository
{
    public function findByUserId(int $userId)
    {
        return Company::where('account_id', $userId)->first();
    }

    public function createForUser(int $userId, array $data)
    {
        return Company::create(array_merge($data, ['account_id' => $userId]));
    }

    public function update(int $companyId, array $data)
    {
        $company = Company::findOrFail($companyId);
        $company->update($data);
        return $company;
    }

    public function delete(int $companyId)
    {
        return Company::where('id', $companyId)->delete();
    }
}
