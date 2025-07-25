<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserKeyController extends Controller
{
    public function resetTransactionKey(User $user)
    {
        $this->authorizeBusiness($user);

        if ($user->keys_locked_by_admin) {
            return response()->json(['error' => 'Key is locked by admin.'], 403);
        }

        $user->transaction_key = Str::random(32);
        $user->transaction_key_enabled = true;
        $user->save();

        LogHelper::log('users', "Generated new transaction key", request()->user()->id, $user->id);

        return response()->json(['transaction_key' => $user->transaction_key]);
    }

    public function resetFiscalKey(User $user)
    {
        $this->authorizeBusiness($user);

        if ($user->keys_locked_by_admin) {
            return response()->json(['error' => 'Key is locked by admin.'], 403);
        }

        $user->fiscal_key = Str::random(32);
        $user->fiscal_key_enabled = true;
        $user->save();

        LogHelper::log('users', "Generated new fiscal key", request()->user()->id, $user->id);

        return response()->json(['fiscal_key' => $user->fiscal_key]);
    }

    public function toggleTransactionKey(User $user)
    {
        $this->authorizeBusiness($user);

        if ($user->keys_locked_by_admin) {
            return response()->json(['error' => 'Key is locked by admin.'], 403);
        }

        $user->transaction_key_enabled = !$user->transaction_key_enabled;
        $user->save();

        LogHelper::log('users', "Toggled transaction key. New state: ".($user->transaction_key_enabled ? 'Enabled' : 'Disabled'), request()->user()->id, $user->id);

        return response()->json(['enabled' => $user->transaction_key_enabled]);
    }

    public function toggleFiscalKey(User $user)
    {
        $this->authorizeBusiness($user);

        if ($user->keys_locked_by_admin) {
            return response()->json(['error' => 'Key is locked by admin.'], 403);
        }

        $user->fiscal_key_enabled = !$user->fiscal_key_enabled;
        $user->save();

        LogHelper::log('users', "Toggled fiscal key. New state: ".($user->fiscal_key_enabled ? 'Enabled' : 'Disabled'), request()->user()->id, $user->id);

        return response()->json(['enabled' => $user->fiscal_key_enabled]);
    }

    public function toggleLock(Request $request, User $user)
    {
        $data = $request->validate([
            'locked' => 'required|boolean',
        ]);

        $user->keys_locked_by_admin = $data['locked'];
        $user->save();

        LogHelper::log('users', "Toggled key administrative lock. New state: ".($user->keys_locked_by_admin ? 'Enabled' : 'Disabled'), request()->user()->id, $user->id);

        return response()->json(['locked' => $data['locked']]);
    }

    private function authorizeBusiness(User $user)
    {
        if ($user->role !== 'business') {
            return redirect('/dashboard')->with("error", 'Only businesses can have keys!');
        }
    }
}
