<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(
        protected User $userModel
    ) {}
    /**
     * Get user by given int id
     * @param int $userId
     * @return User|null
     */
    public function getUserById($userId)
    {
        return $this->userModel->find($userId);
    }

    public function checkNumber($number)
    {
        return !User::where('account_number', $number)->first();
    }

    public function checkCardNumber($number)
    {
        return !User::where('card_number', $number)->first();
    }

    public function listByRole($role)
    {
        return User::where('role', $role)->get();
    }
}