<?php

namespace DuncanMcClean\Cargo\Orders;

use ArrayAccess;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Orders\Order as Contract;
use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Data\HasAddresses;
use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderDeleted;
use DuncanMcClean\Cargo\Events\OrderSaved;
use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Facades\Order as OrderFacade;
use DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway;
use DuncanMcClean\Cargo\Shipping\ShippingMethod;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Searchable as SearchableContract;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Search\Searchable;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Order implements Arrayable, ArrayAccess, Augmentable, ContainsQueryableValues, Contract, SearchableContract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAddresses, HasAugmentedInstance, HasDirtyState, HasTotals, Searchable, TracksQueriedColumns, TracksQueriedRelations;

    protected $id;
    protected $orderNumber;
    protected $date;
    protected $cart;
    protected $status;
    protected $customer;
    protected $lineItems;
    protected $site;
    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
        $this->lineItems = new LineItems;
        $this->status = OrderStatus::PaymentPending->value;
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        $this->supplements = clone $this->supplements;
        $this->lineItems = clone $this->lineItems;
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function orderNumber($orderNumber = null)
    {
        return $this
            ->fluentlyGetOrSet('orderNumber')
            ->args(func_get_args());
    }

    public function date($date = null)
    {
        return $this
            ->fluentlyGetOrSet('date')
            ->setter(function ($date) {
                if ($date === null) {
                    return null;
                }

                if ($date instanceof \Carbon\CarbonInterface) {
                    return $date->utc();
                }

                return $this->parseDateFromString($date);
            })
            ->args(func_get_args());
    }

    private function parseDateFromString($date)
    {
        if (strlen($date) === 10) {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        }

        if (strlen($date) === 15) {
            return Carbon::createFromFormat('Y-m-d-Hi', $date)->startOfMinute();
        }

        return Carbon::createFromFormat('Y-m-d-His', $date);
    }

    public function hasTime()
    {
        return $this->date && ! $this->date->isStartOfDay();
    }

    public function hasSeconds()
    {
        if (! $this->hasTime()) {
            return false;
        }

        return $this->date && $this->date->second !== 0;
    }

    public function cart($cart = null)
    {
        return $this
            ->fluentlyGetOrSet('cart')
            ->setter(function ($cart) {
                if ($cart instanceof Cart) {
                    return $cart->id();
                }

                return $cart;
            })
            ->args(func_get_args());
    }

    public function status($status = null)
    {
        return $this
            ->fluentlyGetOrSet('status')
            ->getter(function ($status) {
                if (! $status) {
                    return OrderStatus::PaymentPending;
                }

                return OrderStatus::from($status);
            })
            ->setter(function ($status) {
                if ($status instanceof OrderStatus) {
                    return $status->value;
                }

                return $status;
            })
            ->args(func_get_args());
    }

    public function customer($customer = null)
    {
        return $this
            ->fluentlyGetOrSet('customer')
            ->getter(function ($customer) {
                if (! $customer) {
                    return null;
                }

                if (is_array($customer)) {
                    return (new GuestCustomer)->data($customer);
                }

                return User::find($customer);
            })
            ->setter(function ($customer) {
                if (! $customer) {
                    return null;
                }

                if ($customer instanceof Authenticatable) {
                    return $customer->getKey();
                }

                if ($customer instanceof GuestCustomer) {
                    return $customer->toArray();
                }

                return $customer;
            })
            ->args(func_get_args());
    }

    public function lineItems($lineItems = null)
    {
        return $this
            ->fluentlyGetOrSet('lineItems')
            ->setter(function ($lineItems) {
                // When we're creating an order from a cart, let's allow the actual LineItems
                // instance to be passed instead of casting to/from an array.
                if ($lineItems instanceof LineItems) {
                    return $lineItems;
                }

                $items = new LineItems;

                collect($lineItems)->each(fn (array $lineItem) => $items->create($lineItem));

                return $items;
            })
            ->args(func_get_args());
    }

    public function shippingMethod(): ?ShippingMethod
    {
        if (! $this->get('shipping_method')) {
            return null;
        }

        return Facades\ShippingMethod::find($this->get('shipping_method'));
    }

    public function shippingOption(): ?ShippingOption
    {
        if (! $this->shippingMethod() || ! $this->get('shipping_option')) {
            return null;
        }

        return ShippingOption::make($this->shippingMethod())
            ->name(Arr::get($this->get('shipping_option'), 'name'))
            ->handle(Arr::get($this->get('shipping_option'), 'handle'))
            ->price(Arr::get($this->get('shipping_option'), 'price'));
    }

    public function paymentGateway(): ?PaymentGateway
    {
        if (! $this->get('payment_gateway')) {
            return null;
        }

        return Facades\PaymentGateway::find($this->get('payment_gateway'));
    }

    public function timelineEvents(): Collection
    {
        return collect($this->get('timeline_events', []))
            ->map(fn (array $event) => TimelineEvent::make($event));
    }

    public function appendTimelineEvent(string|TimelineEventType $eventType, array $metadata = []): self
    {
        if (is_subclass_of($eventType, TimelineEventType::class)) {
            $eventType = $eventType::handle();
        }

        $events = $this->get('timeline_events', []);

        $events[] = Arr::removeNullValues([
            'timestamp' => now()->timestamp,
            'type' => $eventType,
            'user' => Auth::id(),
            'metadata' => $metadata,
        ]);

        $this->set('timeline_events', $events);

        return $this;
    }

    public function site($site = null)
    {
        return $this
            ->fluentlyGetOrSet('site')
            ->setter(function ($site) {
                return $site instanceof \Statamic\Sites\Site ? $site->handle() : $site;
            })
            ->getter(function ($site) {
                if (! $site) {
                    return Site::default();
                }

                if ($site instanceof \Statamic\Sites\Site) {
                    return $site;
                }

                return Site::get($site);
            })
            ->args(func_get_args());
    }

    public function saveQuietly(): bool
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save(): bool
    {
        $original = $this->getOriginal();
        $isNew = is_null(OrderFacade::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        OrderFacade::save($this);

        if ($withEvents) {
            if ($isNew) {
                OrderCreated::dispatch($this);
            }

            OrderSaved::dispatch($this);

            if ($this->status()->value !== Arr::get($original, 'status')) {
                if ($originalStatus = Arr::get($original, 'status')) {
                    OrderStatusUpdated::dispatch($this, OrderStatus::from($originalStatus), $this->status());
                }

                $event = OrderStatus::event($this->status());
                $event::dispatch($this);
            }
        }

        $this->syncOriginal();

        return true;
    }

    public function deleteQuietly(): bool
    {
        $this->withEvents = false;

        return $this->delete();
    }

    public function delete(): bool
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        OrderFacade::delete($this);

        if ($withEvents) {
            OrderDeleted::dispatch($this);
        }

        return true;
    }

    public function path(): string
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath(): string
    {
        $prefix = '';

        if ($this->date) {
            $format = 'Y-m-d';

            if ($this->hasTime()) {
                $format .= '-Hi';
            }

            if ($this->hasSeconds()) {
                $format .= 's';
            }

            $prefix = $this->date->format($format).'.';
        }

        return vsprintf('%s/%s%s%s.yaml', [
            rtrim(Stache::store('orders')->directory(), '/'),
            Site::multiEnabled() ? $this->site()->handle().'/' : '',
            $prefix,
            $this->orderNumber(),
        ]);
    }

    public function fileData(): array
    {
        return $this->data()->merge([
            'id' => $this->id(),
            'cart' => $this->cart(),
            'status' => $this->status()->value,
            'customer' => $this->customer,
            'line_items' => $this->lineItems()->map->fileData()->all(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ])->filter()->all();
    }

    public function fresh(): ?Order
    {
        return OrderFacade::find($this->id());
    }

    public function blueprint(): StatamicBlueprint
    {
        return OrderFacade::blueprint();
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'order_number', 'date', 'status', 'grand_total', 'sub_total', 'discount_total', 'tax_total', 'shipping_total'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedOrder($this);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'order_number' => $this->orderNumber(),
            'date' => $this->date(),
            'cart' => $this->cart(),
            'status' => $this->status()?->value,
            'customer' => $this->customer(),
            'line_items' => $this->lineItems(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ], $this->data()->toArray());
    }

    public function editUrl(): string
    {
        return cp_route('cargo.orders.edit', $this->id());
    }

    public function updateUrl(): string
    {
        return cp_route('cargo.orders.update', $this->id());
    }

    public function reference(): string
    {
        return "order::{$this->id()}";
    }

    public function getCpSearchResultBadge(): string
    {
        return __('Orders');
    }

    public function getQueryableValue(string $field)
    {
        if ($field === 'status') {
            return $this->status()->value;
        }

        if ($field === 'customer') {
            if (is_array($this->customer)) {
                return $this->customer()->id();
            }

            return $this->customer;
        }

        if (method_exists($this, $method = Str::camel($field))) {
            return $this->{$method}();
        }

        $value = $this->get($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }
}
