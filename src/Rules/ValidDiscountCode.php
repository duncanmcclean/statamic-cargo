<?php

namespace DuncanMcClean\Cargo\Rules;

use Closure;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDiscountCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        if (! Discount::query()->where('code', $value)->first()) {
            $fail('cargo::validation.invalid_discount_code')->translate();
        }
    }
}
