<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Repositories\UserRepository;

class GenerateCardNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan users:generate-card-numbers
     */
    protected $signature = 'users:generate-card-numbers';

    /**
     * The console command description.
     */
    protected $description = 'Generate and assign new card numbers for all users';

    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $users = User::all();

        $this->info("Found {$users->count()} users. Generating card numbers...");

        foreach ($users as $user) {
            $cardNumber = $this->generateCardNumber();

            $user->card_number = $cardNumber;
            $user->save();

            $this->line("User #{$user->id}: {$cardNumber}");
        }

        $this->info('All users have been assigned new card numbers.');
        return self::SUCCESS;
    }

    private function generateCardNumber(): string
    {
        do {
            $cardNumber = rand(10000000, 99999999) . '-' . rand(10000000, 99999999);
        } while (!$this->userRepository->checkCardNumber($cardNumber));

        return $cardNumber;
    }
}
