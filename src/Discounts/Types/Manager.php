<?php

namespace DuncanMcClean\Cargo\Discounts\Types;

use Illuminate\Support\Collection;

class Manager
{
    public function all()
    {
        return $this->classes()->map(fn ($class) => app($class));
    }

    public function find(string $handle)
    {
        if (! $this->classes()->has($handle)) {
            return;
        }

        return app($this->classes()->get($handle));
    }

    protected function classes(): Collection
    {
        return app('statamic.extensions')[DiscountType::class];
    }
}
