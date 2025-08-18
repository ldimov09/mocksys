<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Repositories\DeviceRepository;
use Illuminate\Support\Facades\Log;

class VerifyDevice
{
    protected $deviceRepository;

    public function __construct(DeviceRepository $deviceRepository)
    {
        $this->deviceRepository = $deviceRepository;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info("DEVICE CHECK INIT");
        $deviceKey = $request->header('X-Device-Key');

        if (!$deviceKey) {
            return response()->json(['message' => 'Missing device key'], 400);
        }

        $device = $this->deviceRepository->findByKey($deviceKey);

        if (!$device || $device->status !== 'enabled') {
            return response()->json(['message' => 'Invalid or disabled device.'], 403);
        }

        // Attach the device to the request for later use
        $request->attributes->set('device', $device);
        
        Log::info("DEVICE CHECK PASSED");
        return $next($request);
    }
}
