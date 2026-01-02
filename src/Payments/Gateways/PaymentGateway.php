<?php

namespace DuncanMcClean\Cargo\Payments\Gateways;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class PaymentGateway
{
    use HasHandle, HasTitle, RegistersItself;

    public function isAvailable(Cart $cart): bool
    {
        return true;
    }

    abstract public function setup(Cart $cart): array;

    abstract public function process(Order $order): void;

    abstract public function capture(Order $order): void;

    abstract public function cancel(Cart $cart): void;

    abstract public function webhook(Request $request): Response;

    abstract public function refund(Order $order, int $amount): void;

    public function logo(): ?string
    {
        return null;
    }

    public function fieldtypeDetails(Order $order): array
    {
        return [
            __('Amount') => Money::format($order->grandTotal(), $order->site()),
        ];
    }

    public function config(): Collection
    {
        return collect(config("statamic.cargo.payments.gateways.{$this->handle()}"));
    }

    public function checkoutUrl(): string
    {
        return route('statamic.cargo.payments.checkout', $this->handle());
    }

    public function webhookUrl(): string
    {
        if (app()->runningUnitTests()) {
            return '';
        }

        return route('statamic.cargo.payments.webhook', $this->handle());
    }
}
