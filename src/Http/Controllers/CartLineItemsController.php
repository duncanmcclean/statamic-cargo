<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Cart\Actions\AddToCart;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Http\Requests\Cart\AddLineItemRequest;
use DuncanMcClean\Cargo\Http\Requests\Cart\UpdateLineItemRequest;
use DuncanMcClean\Cargo\Http\Resources\API\CartResource;
use DuncanMcClean\Cargo\Products\Actions\ValidateStock;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;

class CartLineItemsController
{
    use Concerns\HandlesCustomerInformation;

    public function store(AddLineItemRequest $request)
    {
        $cart = Cart::current();
        $product = Product::find($request->product);
        $variant = $request->variant ? $product->variant($request->variant) : null;

        $data = $request->collect()->except([
            '_token', '_method', '_redirect', '_error_redirect', 'product', 'variant', 'quantity', 'first_name', 'last_name', 'email', 'customer',
        ]);

        app(AddToCart::class)->handle(
            $cart,
            $product,
            $variant,
            $request->quantity,
            $data
        );

        $cart = $this->handleCustomerInformation($request, $cart);

        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function update(UpdateLineItemRequest $request, string $lineItem)
    {
        $cart = Cart::current();
        $lineItem = $cart->lineItems()->find($lineItem);

        throw_if(! $lineItem, NotFoundHttpException::class);

        $data = $request->collect()->except([
            '_token', '_method', '_redirect', '_error_redirect', 'product', 'variant', 'quantity', 'first_name', 'last_name'.'email', 'customer',
        ]);

        $variant = $lineItem->variant();

        if ($request->variant) {
            $variant = $lineItem->product()->variant($request->variant);
        }

        app(ValidateStock::class)->handle(
            lineItem: $lineItem,
            variant: $variant,
            quantity: $request->quantity ?? $lineItem->quantity,
        );

        $cart->lineItems()->update(
            id: $lineItem->id(),
            data: $lineItem->data()->merge($data)->merge([
                'variant' => $request->variant ?? $lineItem->variant,
                'quantity' => $request->quantity ?? $lineItem->quantity(),
            ])->all()
        );

        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function destroy(Request $request, string $lineItem)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        $cart = Cart::current();
        $lineItem = $cart->lineItems()->find($lineItem);

        throw_if(! $lineItem, NotFoundHttpException::class);

        $cart->lineItems()->remove($lineItem->id());
        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
