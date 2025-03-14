<?php

namespace DuncanMcClean\Cargo\Payments\Gateways;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Dummy extends PaymentGateway
{
    public function setup(Cart $cart): array
    {
        return [];
    }

    public function process(Order $order): void
    {
        $order->status(OrderStatus::PaymentReceived)->save();
    }

    public function capture(Order $order): void
    {
        //
    }

    public function cancel(Cart $cart): void
    {
        //
    }

    public function webhook(Request $request): Response
    {
        return response();
    }

    public function refund(Order $order, int $amount): void
    {
        $order->set('amount_refunded', $amount)->save();
    }
}
