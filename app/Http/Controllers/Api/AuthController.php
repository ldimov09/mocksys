<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController {
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'account_number' => 'required|string',
            'password' => 'required|string',
            'specialPassword' => 'string',
        ]);

        $user = User::where('account_number', $credentials['account_number'])->first();

        if ($user && Hash::check($credentials['password'], (string) $user->password)) {
            if(!$user->status->isEnabled()){
                LogHelper::log('authentication', "No access granted due to disabled account", $user->id);
                return back()->with('error', 'No access granted due to disabled account');
            }

            LogHelper::log('authentication', "Logged in: Account number: ".$credentials['account_number'], $user->id);
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        }

        LogHelper::log('authentication', "Invalid login credentials: Account number: ".$credentials['account_number'], null);
        return response()->json([
            'success' => false,
            'error' => "Invalid login credentials!"
        ], 400);
    }
}