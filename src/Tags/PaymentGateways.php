<?php

namespace DuncanMcClean\Cargo\Tags;

use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Facades\Cart as CartFacade;
use DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway;
use Statamic\Facades\Blink;
use Statamic\Tags\Tags;

class PaymentGateways extends Tags
{
    const BLINK_KEY = 'payment-gateways-loop';

    public function index()
    {
        $cart = CartFacade::current();

        if (! Blink::has(self::BLINK_KEY)) {
            Blink::put(self::BLINK_KEY, $this->getPaymentGateways($cart));
        }

        return Blink::get(self::BLINK_KEY);
    }

    private function getPaymentGateways($cart)
    {
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
