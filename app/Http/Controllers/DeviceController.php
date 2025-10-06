<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\DeviceRepository;
use App\Repositories\UserRepository;

class DeviceController extends Controller
{
    public function __construct(
        private DeviceRepository $devices,
        private UserRepository $users
    ) {}

    public function index(Request $request)
    {
        $selectedUserId = $request->query('user_id');
        $businessUsers = $this->users->listByRole('business');
        $devices = [];

        if ($selectedUserId) {
            $devices = $this->devices->listByUser($selectedUserId);
        }

        return view('admin.devices.index', compact('businessUsers', 'selectedUserId', 'devices'));
    }

    public function create(Request $request)
    {
        $userId = $request->query('user_id');
        return view('admin.devices.create', compact('userId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_name' => 'required|string|max:255',
            'device_address' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'number' => 'required|string|max:50|unique:devices,number',
            'status' => 'required|in:enabled,disabled',
        ]);

        $validated['device_key'] = bin2hex(random_bytes(8));

        $this->devices->create($validated);

        return redirect()->route('admin.devices.index', ['user_id' => $validated['user_id']])
                         ->with('success', 'Device created successfully.');
    }

    public function edit(int $id)
    {
        $device = $this->devices->findById($id);
        return view('admin.devices.edit', compact('device'));
    }

    public function update(int $id, Request $request)
    {
        $device = $this->devices->findById($id);

        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_address' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'number' => 'required|string|max:50|unique:devices,number,' . $device->id,
            'status' => 'required|in:enabled,disabled',
        ]);

        if ($request->get('new_key')) {
            $validated['device_key'] = bin2hex(random_bytes(8));
        }

        $this->devices->update($device, $validated);

        return redirect()->route('admin.devices.index', ['user_id' => $device->user_id])
                         ->with('success', 'Device updated successfully.');
    }

    public function destroy(int $id)
    {
        $device = $this->devices->findById($id);
        $userId = $device->user_id;
        $this->devices->delete($device);

        return redirect()->route('admin.devices.index', ['user_id' => $userId])
                         ->with('success', 'Device deleted successfully.');
    }
}
