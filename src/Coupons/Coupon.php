<?php

namespace DuncanMcClean\Cargo\Coupons;

use ArrayAccess;
use Carbon\Carbon;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Coupons\Coupon as Contract;
use DuncanMcClean\Cargo\Events\CouponCreated;
use DuncanMcClean\Cargo\Events\CouponDeleted;
use DuncanMcClean\Cargo\Events\CouponSaved;
use DuncanMcClean\Cargo\Facades\Coupon as CouponFacade;
use DuncanMcClean\Cargo\Facades\Order as OrderFacade;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Coupon implements Arrayable, ArrayAccess, Augmentable, ContainsQueryableValues, Contract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, HasDirtyState, TracksQueriedColumns, TracksQueriedRelations;

    protected $id;
    protected $code;
    protected $amount;
    protected $type;
    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function code($id = null)
    {
        return $this
            ->fluentlyGetOrSet('code')
            ->setter(function ($code) {
                return Str::upper($code);
            })
            ->args(func_get_args());
    }

    public function type($type = null)
    {
        return $this
            ->fluentlyGetOrSet('type')
            ->getter(function ($type) {
                if (! $type) {
                    return null;
                }

                return CouponType::from($type);
            })
            ->setter(function ($type) {
                if ($type instanceof CouponType) {
                    return $type->value;
                }

                return $type;
            })
            ->args(func_get_args());
    }

    public function amount($amount = null)
    {
        return $this
            ->fluentlyGetOrSet('amount')
            ->setter(function ($amount) {
                if (is_string($amount) && str_contains($amount, '.')) {
                    $amount = (int) str_replace('.', '', $amount);
                }

                if ($this->type() === CouponType::Percentage && $amount > 100) {
                    return (int) $amount / 100;
                }

                return (int) $amount;
            })
            ->args(func_get_args());
    }

    public function isValid(Cart $cart, LineItem $lineItem): bool
    {
        if ($this->get('valid_from') !== null) {
            if (Carbon::parse($this->get('valid_from'))->isFuture()) {
                return false;
            }
        }

        if ($this->has('expires_at') && $this->get('expires_at') !== null) {
            if (Carbon::parse($this->get('expires_at'))->isPast()) {
                return false;
            }
        }

        if ($this->has('minimum_cart_value') && $cart->itemsTotal()) {
            if ($cart->itemsTotal() < $this->get('minimum_cart_value')) {
                return false;
            }
        }

        if ($this->has('maximum_uses') && $this->get('maximum_uses') !== null) {
            if ($this->redeemedCount() >= $this->get('maximum_uses')) {
                return false;
            }
        }

        if ($this->isProductSpecific() && ! in_array($lineItem->product()->id(), $this->get('products'))) {
            return false;
        }

        if ($this->isCustomerSpecific()) {
            if (! $cart->customer()) {
                return false;
            }

            if (! collect($this->get('customers'))->contains($cart->customer()?->id())) {
                return false;
            }
        }

        if ($this->customerEligibility() === 'customers_by_domain' && $domains = $this->get('customers_by_domain')) {
            if (! $cart->customer()) {
                return false;
            }

            if (! collect($domains)->contains(Str::after($cart->customer()->email(), '@'))) {
                return false;
            }
        }

        return true;
    }

    protected function isProductSpecific(): bool
    {
        return $this->has('products') && collect($this->get('products'))->count() >= 1;
    }

    protected function customerEligibility(): string
    {
        return $this->get('customer_eligibility') ?? 'all';
    }

    protected function isCustomerSpecific(): bool
    {
        return
            $this->customerEligibility() === 'specific_customers'
            && $this->has('customers')
            && collect($this->get('customers'))->count() >= 1;
    }

    public function redeemedCount(): int
    {
        return OrderFacade::query()
            ->where('coupon', $this->id())
            ->count();
    }

    public function discountText(): string
    {
        return match ($this->type()) {
            CouponType::Percentage => __('cargo::messages.coupon_discount_text', ['amount' => "{$this->amount()}%"]),
            CouponType::Fixed => __('cargo::messages.coupon_discount_text', ['amount' => Money::format($this->amount(), Site::current())]),
        };
    }

    public function saveQuietly(): bool
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save(): bool
    {
        $isNew = is_null(CouponFacade::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        CouponFacade::save($this);

        if ($withEvents) {
            if ($isNew) {
                CouponCreated::dispatch($this);
            }

            CouponSaved::dispatch($this);
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

        CouponFacade::delete($this);

        if ($withEvents) {
            CouponDeleted::dispatch($this);
        }

        return true;
    }

    public function path(): string
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath(): string
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('coupons')->directory(), '/'),
            $this->code(),
        ]);
    }

    public function fileData(): array
    {
        return array_merge([
            'id' => $this->id(),
            'amount' => $this->amount(),
            'type' => $this->type()?->value,
        ], $this->data->all());
    }

    public function fresh(): ?Coupon
    {
        return CouponFacade::find($this->id());
    }

    public function blueprint()
    {
        return CouponFacade::blueprint();
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'code', 'type', 'amount'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedCoupon($this);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'code' => $this->code(),
            'amount' => $this->amount(),
            'type' => $this->type()?->value,
        ], $this->data()->toArray());
    }

    public function editUrl()
    {
        return cp_route('cargo.coupons.edit', $this->id());
    }

    public function updateUrl()
    {
        return cp_route('cargo.coupons.update', $this->id());
    }

    public function reference(): string
    {
        return "coupon::{$this->id()}";
    }

    public function getQueryableValue(string $field)
    {
        if ($field === 'type') {
            return $this->type()->value;
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
