<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Cart\Cart;
use DuncanMcClean\Cargo\Orders\LineItem;

class CalculateLineItems
{
    protected static $priceHook;

    public function handle(Cart $cart, Closure $next)
    {
        $cart->lineItems()->each(function (LineItem $lineItem) use ($cart) {
            $product = $lineItem->product();

            $price = match (true) {
                isset(static::$priceHook) => (static::$priceHook)($cart, $lineItem),
                $product->isStandardProduct() => $product->price(),
                $product->isVariantProduct() => $product->variant($lineItem->variant()->key())->price(),
            };

            $lineItem->unitPrice($price);
            $lineItem->subTotal($price * $lineItem->quantity());
            $lineItem->total($lineItem->subTotal());
        });

        $cart->subTotal($cart->lineItems()->map->subTotal()->sum());

        return $next($cart);
    }

    public static function priceHook(?Closure $closure)
    {
        static::$priceHook = $closure;

        return new static;
    }
}
