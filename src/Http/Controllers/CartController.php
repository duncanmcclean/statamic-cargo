<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Http\Requests\Cart\UpdateCartRequest;
use DuncanMcClean\Cargo\Http\Resources\API\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Exceptions\NotFoundHttpException;

class CartController
{
    use Concerns\HandlesCustomerInformation, Concerns\RedeemsCoupons;

    public function index(Request $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        return new CartResource(Cart::current());
    }

    public function update(UpdateCartRequest $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        $cart = Cart::current();
        $validated = $request->validated();

        $cart = $this->handleCustomerInformation($request, $cart);
        $cart = $this->redeemCoupon($request, $cart);

        $cart->merge(Arr::except($validated, ['coupon', 'customer']));
        $cart->save();

        if ($request->ajax() || $request->wantsJson()) {
            return new CartResource($cart->fresh());
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }

    public function destroy(Request $request)
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        Cart::current()->delete();
        Cart::forgetCurrentCart();

        if ($request->ajax() || $request->wantsJson()) {
            return [];
        }

        return $request->_redirect ? redirect($request->_redirect) : back();
    }
}
