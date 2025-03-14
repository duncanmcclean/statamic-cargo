<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Orders\OrderRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderRepository::class;
    }
}
