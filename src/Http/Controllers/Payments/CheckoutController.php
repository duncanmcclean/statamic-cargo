<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Payments;

use DuncanMcClean\Cargo\Exceptions\PreventCheckout;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Facades\PaymentGateway;
use DuncanMcClean\Cargo\Orders\Actions\CreateOrderFromCart;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Sites\Site;

class CheckoutController
{
    public function __invoke(Request $request, ?string $paymentGateway = null)
    {
        $cart = Cart::current();

        if ($cart->isPaid()) {
            $paymentGateway = PaymentGateway::find($paymentGateway);

            throw_if(! $paymentGateway, NotFoundHttpException::class);
        } else {
            $paymentGateway = null;
        }

        try {
            $order = app(CreateOrderFromCart::class)->handle($cart, $paymentGateway);
        } catch (PreventCheckout $e) {
            $paymentGateway?->cancel($cart);

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
