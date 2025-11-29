<?php

namespace DuncanMcClean\Cargo\Commands\Migration;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;
use stdClass;

use function Laravel\Prompts\progress;

class MigrateOrders extends Command
{
    use \DuncanMcClean\Cargo\Commands\Migration\Concerns\MapsAddresses, \DuncanMcClean\Cargo\Commands\Migration\Concerns\MapsCustomData, \DuncanMcClean\Cargo\Commands\Migration\Concerns\MapsLineItems, \DuncanMcClean\Cargo\Commands\Migration\Concerns\MapsOrderDates, \DuncanMcClean\Cargo\Commands\Migration\Concerns\MapsTimelineEvents, RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:orders';

    protected $description = 'Migrates orders from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $this->migrateOrderBlueprint();

        $repository = Str::afterLast(config('simple-commerce.content.orders.repository'), '\\');

        if ($repository === 'EntryOrderRepository') {
            $this->migrateOrdersFromEntries();

            return;
        }

        if ($repository === 'EloquentOrderRepository') {
            $this
                ->configureEloquentOrders()
                ->bindEloquentRepository()
                ->migrateOrdersFromEloquent();

            return;
        }

        $this->components->error("You're using a custom order repository. You need to migrate your orders manually.");
    }

    private function migrateOrderBlueprint(): self
    {
        $blueprint = match (Str::afterLast(config('simple-commerce.content.orders.repository'), '\\')) {
            'EntryOrderRepository' => Collection::find(config('simple-commerce.content.orders.collection'))?->entryBlueprint(),
            'EloquentOrderRepository' => Blueprint::find('runway::orders'),
        };

        $fieldHandles = $blueprint->fields()->all()->map->handle();
        $additionalFieldHandles = $this->mapCustomData($fieldHandles);

        Blueprint::make('cargo::order')
            ->setContents([
                'tabs' => [
                    'additional' => [
                        'sections' => [[
                            'fields' => $blueprint->fields()->items()->whereIn('handle', $additionalFieldHandles)->values()->all(),
                        ]],
                    ],
                ],
            ])
            ->save();

        $this->components->info("Copied custom fields from the [{$blueprint->handle()}] blueprint to the [cargo::order] blueprint.");

        return $this;
    }

    private function migrateOrdersFromEntries(): self
    {
        $entries = Entry::query()
            ->where('collection', config('simple-commerce.content.orders.collection'))
            ->where('order_status', '!=', 'cart')
            ->lazy();

        if ($entries->isEmpty()) {
            $this->components->warn('No orders found to migrate.');

            return $this;
        }

        progress(
            label: 'Migrating orders',
            steps: $entries,
            callback: function (EntryContract $entry) {
                $data = $entry->data()->merge([
                    'id' => $entry->id(),
                    'date' => $entry->date(),
                    'site' => $entry->site()->handle(),
                ]);

                $this->createOrderFromData($data)->save();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Orders migrated successfully.');

        return $this;
    }

    private function configureEloquentOrders(): self
    {
        $this->call('statamic:cargo:database-orders', [
            '--no-interaction' => true,
        ]);

        $this->newLine();

        return $this;
    }

    private function bindEloquentRepository(): self
    {
        config()->set('statamic.cargo.orders', [
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class,
            'table' => 'cargo_orders',
        ]);

        app()->bind('cargo.orders.eloquent.model', function () {
            return config('statamic.cargo.orders.model', \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class);
        });

        app()->bind('cargo.orders.eloquent.line_items_model', function () {
            return config('statamic.cargo.orders.line_items_model', \DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class,
            \DuncanMcClean\Cargo\Orders\Eloquent\OrderRepository::class
        );

        return $this;
    }

    private function migrateOrdersFromEloquent(): self
    {
        $rows = DB::table('orders')
            ->where('order_status', '!=', 'cart')
            ->orderBy('id')
            ->lazy();

        $statusLogs = DB::table('status_log')
            ->whereIn('order_id', $rows->pluck('id')->all())
            ->orderBy('order_id')
            ->get();

        if ($rows->isEmpty()) {
            $this->components->warn('No orders found to migrate.');

            return $this;
        }

        progress(
            label: 'Migrating orders',
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

                $this->createOrderFromData($data)->save();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Orders migrated successfully.');

        return $this;
    }

    private function createOrderFromData(IlluminateCollection $data): OrderContract
    {
        $status = match ($data->get('order_status')) {
            'placed' && $data->get('payment_status') === 'paid' => 'payment_received',
            'placed' => 'payment_pending',
            'dispatched', 'delivered' => 'shipped',
            'cancelled' => 'cancelled',
        };

        $paymentGateway = isset($data->get('gateway')['use'])
            ? $data->get('gateway')['use']
            : null;

        $gatewayData = isset($data->get('gateway')['data'])
            ? $data->get('gateway')['data']
            : null;

        $shippingMethod = is_array($data->get('shipping_method'))
            ? Arr::first($data->get('shipping_method'))
            : $data->get('shipping_method');

        $discount = $data->has('coupon')
            ? Discount::find($data->get('coupon'))
            : null;

        return (Order::find($data->get('id')) ?? Order::make())
            ->id($data->get('id'))
            ->orderNumber($data->get('order_number'))
            ->date($this->mapOrderDate($data))
            ->status($status)
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
                'shipping_method' => $shippingMethod,
                'shipping_option' => $data->has('shipping_method') ? [
                    'name' => $shippingMethod,
                    'handle' => $shippingMethod,
                    'price' => $data->get('shipping_total', 0),
                ] : null,
                'shipping_tax_breakdown' => $data->has('shipping_tax') ? [
                    [
                        'rate' => $data->get('shipping_tax')['rate'],
                        'description' => 'Unknown',
                        'zone' => 'Unknown',
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
                'timeline_events' => $this->mapTimelineEvents($data),
            ]));
    }
}
