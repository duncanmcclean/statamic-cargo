<?php

namespace DuncanMcClean\Cargo\UpdateScripts;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use Statamic\UpdateScripts\UpdateScript;

class MigrateAddresses extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('v1.0.0-alpha.15');
    }

    public function update()
    {
        $this->console()->info('Migrating addresses to nested format...');

        $this->migrateCarts();
        $this->migrateOrders();

        $this->console()->info('Address migration complete!');
    }

    private function migrateCarts(): void
    {
        $query = Cart::query();

        if ($query->count() === 0) {
            return;
        }

        $query->get()->each(fn (CartContract $cart) => $this->migrateEntity($cart));

        $this->console()->info('Migrated '.Cart::query()->count().' carts.');
    }

    private function migrateOrders(): void
    {
        $query = Order::query();

        if ($query->count() === 0) {
            return;
        }

        $query->get()->each(fn (OrderContract $order) => $this->migrateEntity($order));

        $this->console()->info('Migrated '.Order::query()->count().' orders.');
    }

    private function migrateEntity(CartContract|OrderContract $entity): void
    {
        $data = $entity->data();

        if ($data->has('shipping_address') || $data->has('billing_address')) {
            return;
        }

        if (! $data->has('shipping_line_1') && ! $data->has('billing_line_1')) {
            return;
        }

        $shippingAddress = array_filter([
            'name' => $data->get('shipping_name'),
            'line_1' => $data->get('shipping_line_1'),
            'line_2' => $data->get('shipping_line_2'),
            'city' => $data->get('shipping_city'),
            'postcode' => $data->get('shipping_postcode'),
            'country' => $data->get('shipping_country'),
            'state' => $data->get('shipping_state'),
        ]);

        $billingAddress = array_filter([
            'name' => $data->get('billing_name'),
            'line_1' => $data->get('billing_line_1'),
            'line_2' => $data->get('billing_line_2'),
            'city' => $data->get('billing_city'),
            'postcode' => $data->get('billing_postcode'),
            'country' => $data->get('billing_country'),
            'state' => $data->get('billing_state'),
        ]);

        $newData = $data->except([
            'shipping_name', 'shipping_line_1', 'shipping_line_2', 'shipping_city',
            'shipping_postcode', 'shipping_country', 'shipping_state',
            'billing_name', 'billing_line_1', 'billing_line_2', 'billing_city',
            'billing_postcode', 'billing_country', 'billing_state',
        ]);

        if (! empty($shippingAddress)) {
            $newData = $newData->merge(['shipping_address' => $shippingAddress]);
        }

        if (! empty($billingAddress)) {
            $newData = $newData->merge(['billing_address' => $billingAddress]);
        }

        $entity->data($newData->all());

        if ($entity instanceof OrderContract) {
            $entity->saveQuietly();
        } else {
            $entity->saveWithoutRecalculating();
        }
    }
}
