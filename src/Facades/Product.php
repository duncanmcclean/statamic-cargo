<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Products\ProductRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Products\Product find($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Products\Product findOrFail($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Products\Product fromEntry(\Statamic\Contracts\Entries\Entry $entry)
 *
 * @see \DuncanMcClean\Cargo\Contracts\Products\ProductRepository
 */
class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductRepository::class;
    }
}
