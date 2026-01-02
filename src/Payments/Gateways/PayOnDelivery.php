<?php

namespace DuncanMcClean\Cargo\Payments\Gateways;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Orders\TimelineEvent;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes\OrderStatusChanged;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayOnDelivery extends PaymentGateway
{
    protected static $title = 'Pay on delivery';

    public function isAvailable(Cart $cart): bool
    {
        return $cart->shippingOption()?->acceptsPaymentOnDelivery() ?? false;
    }

    public function setup(Cart $cart): array
    {
        return [];
    }

    public function process(Order $order): void
    {
        //
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

    public function logo(): ?string
    {
        return Cargo::svg('cargo-mark');
    }

    public function fieldtypeDetails(Order $order): array
    {
        $paymentReceivedEvent = $order->timelineEvents()
            ->filter(function (TimelineEvent $timelineEvent): bool {
                return get_class($timelineEvent->type()) === OrderStatusChanged::class
                    && $timelineEvent->metadata()->get('New Status') === 'payment_received';
            })
            ->first();

        return [
            __('Amount') => Money::format($order->grandTotal(), $order->site()),
            __('Payment Date') => $paymentReceivedEvent
                ? __(':datetime UTC', ['datetime' => $paymentReceivedEvent->datetime()->format('Y-m-d H:i:s')])
                : __('Awaiting Payment'),
        ];
    }
}
