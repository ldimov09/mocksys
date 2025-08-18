<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class DeviceRepository
{
    public function listByUser(int $userId)
    {
        return Device::where('user_id', $userId)->get();
    }

    public function findByIdForUser(int $id, int $userId)
    {
        return Device::where('id', $id)->where('user_id', $userId)->firstOrFail();
    }

    public function create(array $data)
    {
        return Device::create($data);
    }

    public function update(Device $device, array $data)
    {
        $device->update($data);
        return $device;
    }

    public function delete(Device $device)
    {
        $device->delete();
    }

    public function findByKey(string $key)
    {
        return Device::where('device_key', $key)->firstOrFail();
    }

    public function doesBelongToCompany(Device $device, int $companyId)
    {
        $company = Company::find($companyId);
        return $device->user_id == $company->account_id;
    }
}
