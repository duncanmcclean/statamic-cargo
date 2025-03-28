<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Cart\CartRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Cart\QueryBuilder query()
 * @method static \DuncanMcClean\Cargo\Contracts\Cart\Cart find($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Cart\Cart findOrFail($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Cart\Cart current()
 * @method static void setCurrent(\DuncanMcClean\Cargo\Contracts\Cart\Cart $cart)
 * @method static bool hasCurrentCart()
 * @method static void forgetCurrentCart()
 * @method static \DuncanMcClean\Cargo\Contracts\Cart\Cart make()
 * @method static void save(\DuncanMcClean\Cargo\Contracts\Cart\Cart $cart)
 * @method static void delete(\DuncanMcClean\Cargo\Contracts\Cart\Cart $cart)
 *
 * @see \DuncanMcClean\Cargo\Contracts\Cart\CartRepository
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CartRepository::class;
    }
}
