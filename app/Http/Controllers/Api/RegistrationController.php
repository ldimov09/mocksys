<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CompanyDigitHelper;
use App\Repositories\CompanyRepository;
use App\Repositories\DeviceRepository;
use App\Repositories\ItemRepository;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private ItemRepository $itemRepository,
        private UserRepository $userRepository,
        private DeviceRepository $deviceRepository
    ) {}
    public function fullRegister(Request $request)
    {
        $validated = $request->validate([
            // Business user
            'business_name' => 'required|string',
            'business_username' => 'required|string|unique:users,user_name',
            'business_password' => 'required|string|min:4',
            // Regular user
            'user_name' => 'required|string',
            'user_username' => 'required|string|unique:users,user_name',
            'user_password' => 'required|string|min:4',
            // Company
            'manager_name' => 'required|string',
            'company_name' => 'required|string',
            'company_address' => 'required|string',
            'legal_form' => 'required|string|in:ead,eood,ood,ad,et', // adjust as needed
        ]);

        // 1. Create Business User with unique account number
        do {
            $accNumber = rand(10000000, 99999999);
        } while (!$this->userRepository->checkNumber($accNumber));

        do {
            $cardNumber = rand(10000000, 99999999) . '-' . rand(1000, 9999);
        } while (!$this->userRepository->checkCardNumber($cardNumber));

        $businessUser = User::create([
            'name' => $validated['business_name'],
            'user_name' => $validated['business_username'],
            'email' => $validated['business_username'] . '@dummy.com',
            'password' => Hash::make($validated['business_password']),
            'account_number' => $accNumber,
            'card_number' => $cardNumber,
            'role' => 'business',
            'balance' => 10000,
            'transaction_key' => Str::random(12),
            'transaction_key_enabled' => 1,
            'fiscal_key' => Str::random(12),
            'fiscal_key_enabled' => 1,
            'status' => "enabled_active",
        ]);

        // 2. Create Regular User with unique account number
        do {
            $accNumber = rand(10000000, 99999999);
        } while (!$this->userRepository->checkNumber($accNumber));

        do {
            $cardNumber = rand(10000000, 99999999) . '-' . rand(1000, 9999);
        } while (!$this->userRepository->checkCardNumber($accNumber));

        $regularUser = User::create([
            'name' => $validated['user_name'],
            'user_name' => $validated['user_username'],
            'email' => $validated['user_username'] . '@dummy.com',
            'password' => Hash::make($validated['user_password']),
            'account_number' => $accNumber,
            'card_number' => $cardNumber,
            'role' => 'user',
            'enabled_active' => true,
            'balance' => 10000,
            'transaction_key' => Str::random(12),
            'transaction_key_enabled' => 1,
            'status' => "enabled_active",
        ]);

        // 3. Create Company tied to Business User
        $company = $this->companyRepository->createForUser($businessUser->id, [
            'manager_name' => $validated['manager_name'],
            'name' => $validated['company_name'],
            'address' => $validated['company_address'],
            'legal_form' => $validated['legal_form'],
            'number' => 'placeholder' // temporary number
        ]);

        // Generate EIK number: pad company ID to 8 digits + check digit
        $baseNumber = str_pad($company->id, 8, '0', STR_PAD_LEFT);
        $checkDigit = CompanyDigitHelper::calculateEIKCheckDigit($baseNumber);
        $company->number = $baseNumber . $checkDigit;
        $company->save();
        $businessUser->company_id = $company->id;
        $businessUser->save();

        // 4. Create 5 dummy items for business user
        for ($i = 1; $i <= 10; $i++) {
            $number = rand(100000, 999999);
            if (!$this->itemRepository->checkNumber($number, $businessUser->id)) {
                continue;
            }

            $this->itemRepository->create([
                'user_id' => $businessUser->id,
                'name' => 'Item ' . $i,
                'short_name' => 'Item ' . $i,
                'price' => rand(1, 10),
                'number' => $number,
                'unit' => 'pcs',
            ]);
        }

        // 5. Create device

        $device = $this->deviceRepository->create([
            'user_id' => $businessUser->id,
            'name' => "POS1",
            'address' => $company->address,
            'description' => "Your POS device",
            'number' => 1,
            'device_key' => bin2hex(random_bytes(8)),
            'status' => 'enabled',
        ]);

        // 6. Log in business user and create token
        $token = $businessUser->createToken('pwa-token')->plainTextToken;
        $businessUser['token'] = $token;

        return response()->json([
            'message' => __('t.login.register_complete'),
            'user' => $businessUser,
            'company_id' => $company->id,
        ], 201);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'user_username' => 'required|string|max:255|unique:users,user_name',
            'user_password' => 'required|string|min:4',
            'role' => 'required|string|in:user,business',
        ]);

        // Generate unique account number
        do {
            $accNumber = rand(10000000, 99999999);
        } while (!$this->userRepository->checkNumber($accNumber));

        // Generate unique card number
        do {
            $cardNumber = rand(10000000, 99999999) . '-' . rand(1000, 9999);
        } while (!$this->userRepository->checkCardNumber($cardNumber));

        $user = User::create([
            'name' => $validated['user_name'],
            'user_name' => $validated['user_username'],
            'email' => $validated['user_username'] . '@dummy.com',
            'password' => Hash::make($validated['user_password']),
            'account_number' => $accNumber,
            'card_number' => $cardNumber,
            'role' => $validated['role'],
            'balance' => 10000,
            'transaction_key' => Str::random(12),
            'transaction_key_enabled' => 1,
            'status' => "enabled_active",
        ]);

        // Log them in and issue a token
        $token = $user->createToken('pwa-token')->plainTextToken;
        $user['token'] = $token;

        return response()->json([
            'message' => __('t.login.register_complete'),
            'user' => $user,
        ], 201);
    }
}
