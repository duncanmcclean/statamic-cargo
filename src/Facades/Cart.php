<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Cart\CartRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Contracts\Cart\CartRepository
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CartRepository::class;
    }
}
