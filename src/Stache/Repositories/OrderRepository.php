<?php

namespace DuncanMcClean\Cargo\Stache\Repositories;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Contracts\Orders\OrderRepository as RepositoryContract;
use DuncanMcClean\Cargo\Contracts\Orders\QueryBuilder;
use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Events\OrderBlueprintFound;
use DuncanMcClean\Cargo\Exceptions\OrderNotFound;
use DuncanMcClean\Cargo\Orders\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Stache\Stache;

class OrderRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('orders');
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query(): QueryBuilder
    {
        return app(QueryBuilder::class);
    }

    public function find($id): ?Order
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findOrFail($id): Order
    {
        $order = $this->find($id);

        if (! $order) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return $order;
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function makeFromCart(Cart $cart): Order
    {
        $data = $cart->data();

        if ($cart->customer() && ! $cart->customer() instanceof GuestCustomer) {
            $hasExistingOrders = $this->query()
                ->where('customer', $cart->customer()->getKey())
                ->exists();

            if (! $hasExistingOrders) {
                $data->put('new_customer', true);
            }
        }

        return self::make()
            ->cart($cart->id())
            ->site($cart->site())
            ->customer($cart->customer())
            ->lineItems($cart->lineItems())
            ->grandTotal($cart->grandTotal())
            ->subTotal($cart->subTotal())
            ->discountTotal($cart->discountTotal())
            ->taxTotal($cart->taxTotal())
            ->shippingTotal($cart->shippingTotal())
            ->data($data->toArray());
    }

    public function save(Order $order): void
    {
        if (! $order->id()) {
            $order->id($this->stache->generateId());
        }

        if (! $order->date()) {
            $order->date(Carbon::now('UTC'));
        }

        if (! $order->orderNumber()) {
            $order->orderNumber($this->generateOrderNumber());
        }

        $this->store->save($order);
    }

    public function delete(Order $order): void
    {
        $this->store->delete($order);
    }

    private function generateOrderNumber(): int
    {
        return Cache::lock('cargo-order-number', 5)->get(function () {
            $lastOrder = $this->query()->orderByDesc('order_number')->first();

            if (! $lastOrder) {
                return config('statamic.cargo.minimum_order_number', 1000);
            }

            return (int) $lastOrder->orderNumber() + 1;
        });
    }

    public function blueprint(): StatamicBlueprint
    {
        $blueprint = (new Blueprint)();

        OrderBlueprintFound::dispatch($blueprint);

        return $blueprint;
    }

    public static function bindings(): array
    {
        return [
            Order::class => \DuncanMcClean\Cargo\Orders\Order::class,
            QueryBuilder::class => \DuncanMcClean\Cargo\Stache\Query\OrderQueryBuilder::class,
        ];
    }
}
