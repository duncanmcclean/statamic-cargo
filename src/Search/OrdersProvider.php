<?php

namespace DuncanMcClean\Cargo\Search;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Search\Searchables\Provider;

class OrdersProvider extends Provider
{
    protected static $handle = 'orders';

    protected static $referencePrefix = 'order';

    public function find(array $keys): Collection
    {
        return Order::query()->whereIn('id', $keys)->get();
    }

    public function provide(): LazyCollection
    {
        return Order::query()
            ->pluck('id')
            ->map(fn ($id) => "order::{$id}");
    }

    public function contains($searchable): bool
    {
        return $searchable instanceof OrderContract;
    }
}
