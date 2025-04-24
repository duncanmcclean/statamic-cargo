<?php

namespace DuncanMcClean\Cargo\Console\Commands\Migration;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;
use stdClass;

use function Laravel\Prompts\progress;

class MigrateCarts extends Command
{
    use Concerns\MapsAddresses, Concerns\MapsCustomData, Concerns\MapsLineItems, Concerns\MapsOrderDates, RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:carts';

    protected $description = 'Migrates carts from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $repository = Str::afterLast(config('simple-commerce.content.orders.repository'), '\\');

        if ($repository === 'EntryOrderRepository') {
            $this->migrateCartsFromEntries();

            return;
        }

        if ($repository === 'EloquentOrderRepository') {
            $this
                ->configureEloquentCarts()
                ->bindEloquentRepository()
                ->migrateCartsFromEloquent();

            return;
        }

        $this->components->error("You're using a custom order repository. You need to migrate your carts manually.");
    }

    private function migrateCartsFromEntries(): self
    {
        $entries = Entry::query()
            ->where('collection', config('simple-commerce.content.orders.collection'))
            ->where('order_status', 'cart')
            ->lazy();

        if ($entries->isEmpty()) {
            $this->components->warn('No carts found to migrate.');

            return $this;
        }

        progress(
            label: 'Migrating carts',
            steps: $entries,
            callback: function (EntryContract $entry) {
                $data = $entry->data()->merge([
                    'id' => $entry->id(),
                    'date' => $entry->date(),
                    'site' => $entry->site()->handle(),
                ]);

                $this->createCartFromData($data)->saveWithoutRecalculating();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Carts migrated successfully.');

        return $this;
    }

    private function configureEloquentCarts(): self
    {
        $this->call('statamic:cargo:database-carts', [
            '--no-interaction' => true,
        ]);

        $this->newLine();

        return $this;
    }

    private function bindEloquentRepository(): self
    {
        config()->set('statamic.cargo.carts', [
            ...config('statamic.cargo.carts'),
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class,
            'table' => 'cargo_carts',
        ]);

        app()->bind('cargo.carts.eloquent.model', function () {
            return config('statamic.cargo.carts.model', \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class);
        });

        app()->bind('cargo.carts.eloquent.line_items_model', function () {
            return config('statamic.cargo.carts.line_items_model', \DuncanMcClean\Cargo\Cart\Eloquent\LineItemModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class,
            \DuncanMcClean\Cargo\Cart\Eloquent\CartRepository::class
        );

        return $this;
    }

    private function migrateCartsFromEloquent(): self
    {
        $rows = DB::table('orders')
            ->where('order_status', 'cart')
            ->orderBy('id')
            ->lazy();

        $statusLogs = DB::table('status_log')
            ->whereIn('order_id', $rows->pluck('id')->all())
            ->orderBy('order_id')
            ->get();

        if ($rows->isEmpty()) {
            $this->components->warn('No carts found to migrate.');

            return $this;
        }

        progress(
            label: 'Migrating carts',
            steps: $rows,
            callback: function (stdClass $row) use ($statusLogs) {
                $data = collect($row)
                    ->reject(fn ($value, string $key) => in_array($key, ['data', 'customer_id', 'items', 'gateway']))
                    ->merge([
                        ...Json::decode($row->data),
                        'customer' => $row->customer_id,
                        'items' => Json::decode($row->items),
                        'gateway' => Json::decode($row->gateway),
                        'site' => Site::default()->handle(),
                        'status_log' => $statusLogs->where('order_id', $row->id)->toArray(),
                    ]);

                $this->createCartFromData($data)->saveWithoutRecalculating();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Carts migrated successfully.');

        return $this;
    }

    private function createCartFromData(Collection $data): CartContract
    {
        $paymentGateway = isset($data->get('gateway')['use'])
            ? $data->get('gateway')['use']
            : null;

        $gatewayData = isset($data->get('gateway')['data'])
            ? $data->get('gateway')['data']
            : null;

        $discount = $data->has('coupon')
            ? Discount::find($data->get('coupon'))
            : null;

        return (Cart::find($data->get('id')) ?? Cart::make())
            ->id($data->get('id'))
            ->customer($data->get('customer'))
            ->lineItems($this->mapLineItems($data))
            ->site($data->get('site'))
            ->grandTotal($data->get('grand_total', 0))
            ->subTotal($data->get('items_total', 0))
            ->discountTotal($data->get('coupon_total', 0))
            ->taxTotal($data->get('tax_total', 0))
            ->shippingTotal($data->get('shipping_total', 0))
            ->data(array_filter([
                ...$this->mapAddresses($data),
                ...$this->mapCustomData($data),
                'payment_gateway' => $paymentGateway,
                'stripe_payment_intent' => $paymentGateway === 'stripe' && isset($gatewayData['id'])
                    ? $gatewayData['id']
                    : null,
                'mollie_payment_id' => $paymentGateway === 'mollie' && isset($gatewayData['id'])
                    ? $gatewayData['id']
                    : null,
                'shipping_method' => $data->get('shipping_method'),
                'shipping_option' => $data->has('shipping_method') ? [
                    'name' => $data->get('shipping_method'),
                    'handle' => $data->get('shipping_method'),
                    'price' => $data->get('shipping_total', 0),
                ] : null,
                'shipping_tax_breakdown' => $data->has('shipping_tax') ? [
                    [
                        'rate' => $data->get('shipping_tax')['rate'],
                        'description' => 'Unknown',
                        'name' => 'Unknown',
                        'amount' => $data->get('shipping_tax')['amount'],
                    ],
                ] : null,
                'discounts' => $discount ? [
                    [
                        'discount' => $discount->handle(),
                        'description' => $discount->get('discount_code'),
                        'amount' => $data->get('coupon_total', 0),
                    ],
                ] : null,
                'discount_code' => $discount?->get('discount_code'),
            ]));
    }
}
