<?php

namespace DuncanMcClean\Cargo\Rules;

use Closure;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueDiscountValue implements ValidationRule
{
    public function __construct(
        private $except = null,
        private $column = null,
    ) {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->column ??= $attribute;

        $existing = Discount::query()
            ->when(
                is_array($value),
                fn ($query) => $query->whereIn($this->column, $value),
                fn ($query) => $query->where($this->column, $value)
            )
            ->first();

        if (! $existing) {
            return;
        }

        if ($this->except == $existing->handle()) {
            return;
        }

        $fail('cargo::validation.unique_discount_value')->translate();
    }
}
