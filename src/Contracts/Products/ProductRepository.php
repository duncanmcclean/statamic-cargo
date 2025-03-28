<?php

namespace DuncanMcClean\Cargo\Contracts\Products;

use Illuminate\Support\Collection;

interface ProductRepository
{
    public function all(): Collection;

    public function find($id): ?Product;

    public function findOrFail($id): Product;
}
