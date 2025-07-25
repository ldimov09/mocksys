<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const SPECIAL_PASSWORD_HASH = '$2y$12$ysPep0Q.KlPPI8q9yl4rEOfRQMaE6D3aEyeh4QQ3tiGcOROjY3hmm';

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'account_number' => 'required|string',
            'password' => 'required|string',
            'specialPassword' => 'string',
        ]);

        $user = User::where('account_number', $credentials['account_number'])->first();

        if ($user && Hash::check($credentials['password'], (string) $user->password)) {
            if($user->role == 'admin' && !Hash::check($credentials['specialPassword'], self::SPECIAL_PASSWORD_HASH)){
                LogHelper::log('authentication', "No access granted due to incorrect admin password", $user->id);
                return back()->with('error', 'Incorrect special password');
            }

            if(!$user->status->isEnabled()){
                LogHelper::log('authentication', "No access granted due to disabled account", $user->id);
                return back()->with('error', 'No access granted due to disabled account');
            }

            Auth::login($user);
            LogHelper::log('authentication', "Logged in: Account number: ".$credentials['account_number'], $user->id);
            return redirect()->route('dashboard');
        }

        LogHelper::log('authentication', "Invalid login credentials: Account number: ".$credentials['account_number'], null);
        return back()->withErrors(['account_number' => 'Invalid credentials']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * @deprecated Self-registration is no longer supported
     * Registers a new user
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'account_number' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|size:4|confirmed',
            'role' => 'required|in:admin,business,user',
        ]);

        $user = User::create([
            'name' => $data["name"],
            'account_number' => $data["account_number"],
            'email' => $data["email"],
            'password' => Hash::make($data["password"]),
            'role' => $data["role"],
        ]);

        LogHelper::log('authentication', "New user created: Account number: ".$data['account_number'], $user->id);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout()
    {
        LogHelper::log('authentication', "User logged out: Account number: ".Auth::user()->account_number, Auth::user()->id);
        Auth::logout();
        return redirect()->route('login');
    }
}
