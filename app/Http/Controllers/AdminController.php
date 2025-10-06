<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class AdminController extends Controller
{
    public function __construct(
        private \App\Repositories\UserRepository $userRepository
    ) {}

    public function index()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'account_number' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|in:admin,business,user',
            'status' => ['required', new Enum(UserStatus::class)],
            'username' => 'required|string|max:255|unique:users,user_name',
        ]);

        // Generate unique card number
        do {
            $cardNumber = rand(10000000, 99999999) . '-' . rand(1000, 9999);
        } while (!$this->userRepository->checkCardNumber($cardNumber));

        $user = User::create([
            'name' => $data["name"],
            'account_number' => $data["account_number"],
            "card_number" => $cardNumber,
            'email' => $data["email"],
            'status' => $data["status"],
            'password' => Hash::make($data["password"]),
            'role' => $data["role"],
            "transaction_key" => null,
            "transaction_key_enabled" => false,
            "fiscal_key" => null,
            "fiscal_key_enabled" => false,
            "keys_locked_by_admin" => true,
            "user_name" => $data["username"],
        ]);

        LogHelper::log('users', "User created: Account number: " . $user->account_number, request()->user()->id);
        return redirect()->route('admin.users')->with('success', 'User created.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,business,user',
            'status' => ['required', new Enum(UserStatus::class)]
        ]);

        $user->name = $data["name"];
        $user->email = $data["email"];
        $user->role = $data["role"];
        $user->status = $data["status"];

        if ($request->filled('password')) {
            $user->password = Hash::make($data["password"]);
        }

        $user->save();
        LogHelper::log('users', "User updated: Account number: " . $user->account_number, request()->user()->id);
        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'You can\'t delete other admin accounts.');
        }

        LogHelper::log('users', "User deleted: Account number: " . $user->account_number, request()->user()->id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }
}
