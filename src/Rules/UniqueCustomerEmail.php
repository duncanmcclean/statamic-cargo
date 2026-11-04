<?php

namespace DuncanMcClean\Cargo\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\User;
use Statamic\Rules\UniqueUserValue;

class UniqueCustomerEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (config('statamic.cargo.carts.always_checkout_as_guest')) {
            return;
        }

        if (! $user = User::current()) {
            return;
        }

        (new UniqueUserValue(except: $user->id(), column: 'email'))->validate($attribute, $value, $fail);
    }
}
