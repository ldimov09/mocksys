<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Repositories\DeviceRepository;
use App\Repositories\UserRepository;

class DeviceController extends Controller
{
    public function __construct(
        private DeviceRepository $repo,
        private UserRepository $userRepository
    ) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->repo->listByUser($request->user()->id)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string',
            'device_address' => 'required|string',
            'description' => 'required|string',
            'number' => [
                'required',
                'string',
                Rule::unique('devices')->where(fn($q) => $q->where('user_id', $request->user()->id))
            ],
            'status' => 'required|in:enabled,disabled',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['device_key'] = bin2hex(random_bytes(8));

        return response()->json(
            $this->repo->create($validated),
            201
        );
    }

    public function show($id, Request $request)
    {
        $device = $this->repo->findByIdForUser($id, $request->user()->id);
        return response()->json($device);
    }

    public function update($id, Request $request)
    {
        $device = $this->repo->findByIdForUser($id, $request->user()->id);

        $validated = $request->validate([
            'device_name' => 'required|string',
            'device_address' => 'required|string',
            'description' => 'required|string',
            'number' => [
                'required',
                'string',
                Rule::unique('devices')->where(fn($q) => $q->where('user_id', $request->user()->id))->ignore($device->id)
            ],
            'status' => 'required|in:enabled,disabled',
        ]);

        if($request->get("new_key") ?? false){
            $validated['device_key'] = bin2hex(random_bytes(8));
        }

        return response()->json($this->repo->update($device, $validated));
    }

    public function destroy($id, Request $request)
    {
        $device = $this->repo->findByIdForUser($id, $request->user()->id);
        $this->repo->delete($device);

        return response()->noContent();
    }

    public function getDeviceData($deviceKey)
    {
        $device = $this->repo->findByKey($deviceKey);
        if (!$device) {
            return response()->json(['message' => __('t.device.not_found')], 404);
        }

        $user = $this->userRepository->getUserById($device->user_id);
        if (!$user) {
            return response()->json(['message' => __('t.device.user_not_found')], 404);
        }

        $companyId = $user->company_id;

        return response()->json([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'user_account_number' => $user->account_number,
            'device_key' => $device->device_key,
            'transaction_key' => $user->transaction_key,
            'fiscal_key' => $user->fiscal_key,
        ]);
    }
}
