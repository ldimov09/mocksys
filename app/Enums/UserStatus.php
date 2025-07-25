<?php

namespace App\Enums;

enum UserStatus: string
{
    case ENABLED_ACTIVE = 'enabled_active';     // can log in + transact
    case ENABLED_INACTIVE = 'enabled_inactive'; // can log in, no transactions
    case DISABLED_INACTIVE = 'disabled_inactive'; // no login

    /**
     * Displays user-friendly labes
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ENABLED_ACTIVE => 'Enabled (Active)',
            self::ENABLED_INACTIVE => 'Enabled (Inactive)',
            self::DISABLED_INACTIVE => 'Disabled (Inactive)',
        };
    }

    /**
     * Returns an appropriate status colour
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::ENABLED_ACTIVE => 'black',
            self::ENABLED_INACTIVE => 'red',
            self::DISABLED_INACTIVE => 'red',
        };
    }

    /**
     * Tells whether the user is allowed to be part of a transaction.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this == self::ENABLED_ACTIVE;
    }

    /**
     * Tells whether the user is allowed to log in.
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this == self::ENABLED_INACTIVE || $this == self::ENABLED_ACTIVE;
    }
}
