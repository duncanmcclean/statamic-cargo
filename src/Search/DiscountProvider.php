<?php

namespace DuncanMcClean\Cargo\Search;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Search\Searchables\Provider;

class DiscountProvider extends Provider
{
    protected static $handle = 'discounts';

    protected static $referencePrefix = 'discount';

    public function find(array $keys): Collection
    {
        return Discount::query()->whereIn('handle', $keys)->get();
    }

    public function provide(): LazyCollection
    {
        return Discount::query()->lazy();
    }

    public function contains($searchable): bool
    {
        return $searchable instanceof DiscountContract;
    }
}
