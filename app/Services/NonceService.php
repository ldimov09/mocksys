<?php

namespace App\Services;

use App\Models\Nonce;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NonceService
{
    public function generate(string $purpose, int $ttlSeconds = 300): string
    {
        $nonce = Str::random(32);

        Nonce::create([
            'token'      => $nonce,
            'purpose'    => $purpose,
            'expires_at' => now()->addSeconds($ttlSeconds),
            'used'       => false,
        ]);

        return $nonce;
    }

    public function validate(string $nonce, string $purpose): bool
    {
        $record = Nonce::where('token', $nonce)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            return false;
        }

        // mark as used (single-use)
        $record->update(['used' => true]);

        return true;
    }

    public function purgeExpired(): int
    {
        return Nonce::where('expires_at', '<', now())->delete();
    }
}
