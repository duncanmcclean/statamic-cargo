<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Orders\OrderRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Orders\QueryBuilder query()
 * @method static \DuncanMcClean\Cargo\Contracts\Orders\Order find($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Orders\Order findOrFail($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Orders\Order make()
 * @method static \DuncanMcClean\Cargo\Contracts\Orders\Order makeFromCart(\DuncanMcClean\Cargo\Contracts\Cart\Cart $cart)
 * @method static void save(\DuncanMcClean\Cargo\Contracts\Orders\Order $order)
 * @method static void delete(\DuncanMcClean\Cargo\Contracts\Orders\Order $order)
 * @method static \Statamic\Fields\Blueprint blueprint()
 *
 * @see \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderRepository::class;
    }
}
