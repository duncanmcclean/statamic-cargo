<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Payments;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Discounts\Actions\UpdateDiscounts;
use DuncanMcClean\Cargo\Exceptions\PreventCheckout;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Facades\PaymentGateway;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Products\Actions\UpdateStock;
use DuncanMcClean\Cargo\Products\Actions\ValidateStock;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Sites\Site;

class CheckoutController
{
    public function __invoke(Request $request, ?string $paymentGateway = null)
    {
        $cart = Cart::current();

        if (! $cart->isFree()) {
            $paymentGateway = PaymentGateway::find($paymentGateway);

            throw_if(! $paymentGateway, NotFoundHttpException::class);
        } else {
            $paymentGateway = null;
        }

        try {
            $this->ensureProductsAreAvailable($cart, $request);

            throw_if(! $cart->taxableAddress(), new PreventCheckout(__('Order cannot be created without an address.')));
            throw_if(! $cart->customer(), new PreventCheckout(__('Order cannot be created without customer information.')));

            $order = Order::query()->where('cart', $cart->id())->first();

            if (! $order) {
                $order = tap(Order::makeFromCart($cart))->save();
            }

            if ($order->isFree()) {
                $order->status(OrderStatus::PaymentReceived)->save();
            } else {
                $paymentGateway->process($order);
                $order->set('payment_gateway', $paymentGateway::handle())->save();
            }

            app(UpdateStock::class)->handle($order);
            app(UpdateDiscounts::class)->handle($order);
        } catch (ValidationException|PreventCheckout $e) {
            $paymentGateway->cancel($cart);

            if ($order = Order::query()->where('cart', $cart->id())->first()) {
                $order->delete();
            }

            return redirect()
                ->route($this->getCheckoutRoute($cart->site()))
                ->withErrors($e->errors());
        }

        Cart::forgetCurrentCart();

        return redirect()->temporarySignedRoute(
            route: $this->getCheckoutConfirmationRoute($cart->site()),
            expiration: now()->addHour(),
            parameters: ['order_id' => $order->id()]
        );
    }

    private function ensureProductsAreAvailable(CartContract $cart, Request $request): void
    {
        $cart->lineItems()->each(function (LineItem $lineItem) {
            try {
                app(ValidateStock::class)->handle($lineItem);
            } catch (ValidationException) {
                throw new PreventCheckout(__('cargo::validation.products_no_longer_available'));
            }
        });
    }

    private function getCheckoutRoute(Site $site): string
    {
        if ($route = config("statamic.cargo.routes.{$site->handle()}.checkout")) {
            return $route;
        }

        return config('statamic.cargo.routes.checkout');
    }

    private function getCheckoutConfirmationRoute(Site $site): string
    {
        if ($route = config("statamic.cargo.routes.{$site->handle()}.checkout_confirmation")) {
            return $route;
        }

        return config('statamic.cargo.routes.checkout_confirmation');
    }
}
