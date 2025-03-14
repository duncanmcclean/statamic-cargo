<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Products\ProductRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Contracts\Products\ProductRepository
 */
class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductRepository::class;
    }
}
