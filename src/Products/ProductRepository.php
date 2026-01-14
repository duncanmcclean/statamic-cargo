<?php

namespace DuncanMcClean\Cargo\Products;

use DuncanMcClean\Cargo\Contracts\Products\Product;
use DuncanMcClean\Cargo\Contracts\Products\ProductRepository as RepositoryContract;
use DuncanMcClean\Cargo\Exceptions\ProductNotFound;
use Illuminate\Support\Collection;
use Statamic\Facades\Entry;

class ProductRepository implements RepositoryContract
{
    protected array $collections = [];

    public function __construct()
    {
        $this->collections = config('statamic.cargo.products.collections', ['products']);
    }

    public function all(): Collection
    {
        return Entry::query()
            ->whereIn('collection', $this->collections)
            ->get();
    }

    public function find($id): ?Product
    {
        return Entry::query()
            ->whereIn('collection', $this->collections)
            ->where('id', $id)
            ->find($id);
    }

    public function findOrFail($id): Product
    {
        $product = $this->find($id);

        if (! $product) {
            throw new ProductNotFound("Product [{$id}] could not be found.");
        }

        return $product;
    }

    public static function bindings(): array
    {
        return [];
    }
}
