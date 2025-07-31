<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('user_name', $credentials['user_name'])->first();

        if ($user && Hash::check($credentials['password'], (string) $user->password)) {
            if (!$user->status->isEnabled()) {
                LogHelper::log('authentication', "No access granted due to disabled account", $user->id);
                return back()->with('error', 'No access granted due to disabled account');
            }

            LogHelper::log('authentication', "Logged in: User name: " . $credentials['user_name'], $user->id);

            $token = $user->createToken('pwa-token')->plainTextToken;

            $user['token'] = $token;

            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        }

        LogHelper::log('authentication', "Invalid login credentials: User name: " . $credentials['user_name'], null);
        return response()->json([
            'success' => false,
            'error' => "Invalid login credentials!"
        ], 400);
    }

    public function show(string $accountNumber)
    {
        try {
            $user = User::where('account_number', $accountNumber)->first();

            if(!$user){
                return response()->json([
                    'success' => false,
                    'error' => "User does not exist!"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Unexpected error!"
            ], 500);
        }
    }

    public function resetTransactionKey(Request $request)
    {
        $user = $request->user();

        if ($user->keys_locked_by_admin) {
            return response()->json([
                'success' => false,
                'error' => 'Key is locked by admin.'
            ], 403);
        }

        $user->transaction_key = Str::random(32);
        $user->transaction_key_enabled = true;
        $user->save();

        return response()->json([
            'success' => true,
            'transaction_key' => $user->transaction_key
        ]);
    }

    public function resetFiscalKey(Request $request)
    {
        $user = $request->user();

        if ($user->keys_locked_by_admin) {
            return response()->json([
                'success' => false,
                'error' => 'Key is locked by admin.'
            ], 403);
        }

        $user->fiscal_key = Str::random(32);
        $user->fiscal_key_enabled = true;
        $user->save();

        return response()->json([
            'success' => true,
            'fiscal_key' => $user->fiscal_key
        ]);
    }

    public function toggleTransactionKey(Request $request)
    {
        $user = $request->user();

        if ($user->keys_locked_by_admin) {
            return response()->json([
                'success' => false,
                'error' => 'Key is locked by admin.'
            ], 403);
        }

        $user->transaction_key_enabled = !$user->transaction_key_enabled;
        $user->save();

        return response()->json([
            'success' => true,
            'transaction_key_enabled' => $user->transaction_key_enabled
        ]);
    }

    public function toggleFiscalKey(Request $request)
    {
        $user = $request->user();

        if ($user->keys_locked_by_admin) {
            return response()->json([
                'success' => false,
                'error' => 'Key is locked by admin.'
            ], 403);
        }

        $user->fiscal_key_enabled = !$user->fiscal_key_enabled;
        $user->save();

        return response()->json([
            'success' => true,
            'fiscal_key_enabled' => $user->fiscal_key_enabled
        ]);
    }
}
