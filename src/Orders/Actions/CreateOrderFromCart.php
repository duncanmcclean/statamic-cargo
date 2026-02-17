<?php

namespace DuncanMcClean\Cargo\Orders\Actions;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Discounts\Actions\UpdateDiscounts;
use DuncanMcClean\Cargo\Exceptions\PreventCheckout;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway;
use DuncanMcClean\Cargo\Products\Actions\UpdateStock;
use DuncanMcClean\Cargo\Products\Actions\ValidateStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CreateOrderFromCart
{
    public function handle(Cart $cart, ?PaymentGateway $paymentGateway = null): Order
    {
        if ($cart->isPaid() && ! $paymentGateway) {
            throw new InvalidArgumentException('The $paymentGateway argument is required for paid carts.');
        }

        return Cache::lock("cart:{$cart->id()}:checkout", 30)->block(15, function () use ($cart, $paymentGateway) {
            $order = Facades\Order::query()->where('cart', $cart->id())->first();

            if ($order) {
                return $order;
            }

            $this->ensureProductsAreAvailable($cart);

            throw_if(
                ! $cart->taxableAddress(),
                new PreventCheckout(__('Order cannot be created without an address.'))
            );

            throw_if(
                ! $cart->customer(),
                new PreventCheckout(__('Order cannot be created without customer information.'))
            );

            $order = tap(Facades\Order::makeFromCart($cart))->save();

            if ($order->isFree()) {
                $order->status(OrderStatus::PaymentReceived)->save();
            } else {
                $paymentGateway->process($order);
                $order->set('payment_gateway', $paymentGateway::handle())->save();
            }

            app(UpdateStock::class)->handle($order);
            app(UpdateDiscounts::class)->handle($order);

            return $order;
        });
    }

    private function ensureProductsAreAvailable(Cart $cart): void
    {
        $cart->lineItems()->each(function (LineItem $lineItem) {
            try {
                app(ValidateStock::class)->handle($lineItem);
            } catch (ValidationException) {
                throw new PreventCheckout(__('cargo::validation.products_no_longer_available'));
            }
        });
    }
}
