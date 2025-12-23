<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Payments;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Events\DiscountRedeemed;
use DuncanMcClean\Cargo\Events\ProductNoStockRemaining;
use DuncanMcClean\Cargo\Events\ProductStockLow;
use DuncanMcClean\Cargo\Exceptions\PreventCheckout;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Facades\PaymentGateway;
use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Products\Actions\ValidateStock;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

            $this->updateStock($order);
            $this->updateDiscounts($order);
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

    private function updateStock(OrderContract $order): void
    {
        $order->lineItems()->each(function (LineItem $lineItem) {
            if ($lineItem->product()->isStandardProduct() && $lineItem->product()->isStockEnabled()) {
                $product = $lineItem->product();

                // When the Price field isn't localized, we need to update the stock on the origin entry.
                if ($product->hasOrigin() && ! $product->blueprint()->field('stock')?->isLocalizable()) {
                    $product = Product::find($product->origin()->id());
                }

                $product->set('stock', $product->stock() - $lineItem->quantity())->save();

                if ($product->stock() < config('statamic.cargo.products.low_stock_threshold')) {
                    ProductStockLow::dispatch($product);
                }

                if ($product->stock() === 0) {
                    ProductNoStockRemaining::dispatch($product);
                }
            }

            if ($lineItem->product()->isVariantProduct() && $lineItem->variant()->isStockEnabled()) {
                $product = $lineItem->product();

                // When the Product Variants field isn't localized, we need to update the stock on the origin entry.
                if ($product->hasOrigin() && ! $product->blueprint()->field('product_variants')?->isLocalizable()) {
                    $product = Product::find($product->origin()->id());
                }

                $productVariants = $product->productVariants();

                $productVariants['options'] = collect(Arr::get($productVariants, 'options'))->map(function ($variant) use ($lineItem) {
                    if (isset($variant['stock']) && $variant['key'] === $lineItem->variant()->key()) {
                        $variant['stock'] -= $lineItem->quantity();
                    }

                    return $variant;
                })->all();

                $product->set('product_variants', $productVariants)->save();

                if ($product->stock() < config('statamic.cargo.products.low_stock_threshold')) {
                    ProductStockLow::dispatch($product->variant($lineItem->variant()->key()));
                }

                if ($product->stock() === 0) {
                    ProductNoStockRemaining::dispatch($product->variant($lineItem->variant()->key()));
                }
            }
        });
    }

    private function updateDiscounts(OrderContract $order)
    {
        collect($order->get('discount_breakdown'))->each(function ($discount) use ($order) {
            $discount = Discount::find($discount['discount']);
            $discount->set('redemptions_count', $discount->get('redemptions_count', 0) + 1)->saveQuietly();

            DiscountRedeemed::dispatch($discount, $order);
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
