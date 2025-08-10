<?php

namespace App\Http\Controllers\Api;

use App\Services\NonceService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NonceController extends Controller
{
    public function __construct(
        protected NonceService $nonceService
    ) {}
    public function getNonce(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string|max:255'
        ]);

        return response()->json([
            'nonce' => $this->nonceService->generate($request->purpose)
        ]);
    }

}