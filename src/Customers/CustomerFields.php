<?php

namespace DuncanMcClean\Cargo\Customers;

use Statamic\Facades\User;

class CustomerFields
{
    public static function updatable(): array
    {
        return User::blueprint()->fields()->all()->map->handle()
            ->merge(['name', 'first_name', 'last_name', 'email'])
            ->diff(self::protected())
            ->all();
    }

    private static function protected(): array
    {
        return [
            'id',
            'password',
            'password_hash',
            'remember_token',
            'super',
            'roles',
            'groups',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'two_factor_enabled',
            'email_verified_at',
            'preferences',
            'avatar',
            'last_login',
        ];
    }
}
