<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Cart as CartFacade;
use DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway;
use Statamic\Exceptions\NotFoundHttpException;

class CartPaymentGatewaysController
{
    public function __invoke()
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        $cart = CartFacade::current();

        return Facades\PaymentGateway::all()
            ->map(function (PaymentGateway $paymentGateway) use ($cart) {
                $setup = $cart->isFree() ? [] : $paymentGateway->setup($cart);

                return [
                    'name' => $paymentGateway->title(),
                    'handle' => $paymentGateway->handle(),
                    'checkout_url' => $paymentGateway->checkoutUrl(),
                    ...$setup,
                ];
            })
            ->values()
            ->all();
    }
}
