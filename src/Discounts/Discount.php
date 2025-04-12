<?php

namespace DuncanMcClean\Cargo\Discounts;

use ArrayAccess;
use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount as Contract;
use DuncanMcClean\Cargo\Discounts\Types\DiscountType;
use DuncanMcClean\Cargo\Events\DiscountCreated;
use DuncanMcClean\Cargo\Events\DiscountDeleted;
use DuncanMcClean\Cargo\Events\DiscountSaved;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Facades\Discount as DiscountFacade;
use DuncanMcClean\Cargo\Facades\Order as OrderFacade;
use DuncanMcClean\Cargo\Orders\LineItem;
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
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Discount implements Arrayable, ArrayAccess, Augmentable, ContainsQueryableValues, Contract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, HasDirtyState, TracksQueriedColumns, TracksQueriedRelations;

    protected $id;
    protected $name;
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

    public function name($name = null)
    {
        return $this
            ->fluentlyGetOrSet('name')
            ->args(func_get_args());
    }

    public function type($type = null)
    {
        return $this
            ->fluentlyGetOrSet('type')
            ->args(func_get_args());
    }

    public function discountType(): DiscountType
    {
        return Facades\DiscountType::find($this->type())->setDiscount($this);
    }

    // todo: consider removing this method
    public function isValid(Cart $cart, LineItem $lineItem): bool
    {
        return $this->discountType()->isValidForLineItem($cart, $lineItem);
    }

    // todo
    public function redeemedCount(): int
    {
        return OrderFacade::query()
            ->where('coupon', $this->id())
            ->count();
    }

    public function saveQuietly(): bool
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save(): bool
    {
        $isNew = is_null(DiscountFacade::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        DiscountFacade::save($this);

        if ($withEvents) {
            if ($isNew) {
                DiscountCreated::dispatch($this);
            }

            DiscountSaved::dispatch($this);
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

        DiscountFacade::delete($this);

        if ($withEvents) {
            DiscountDeleted::dispatch($this);
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
            rtrim(Stache::store('discounts')->directory(), '/'),
            Str::slug($this->name()),
        ]);
    }

    public function fileData(): array
    {
        return array_merge([
            'id' => $this->id(),
            'name' => $this->name(),
            'type' => $this->type(),
        ], $this->data->all());
    }

    public function fresh(): ?Discount
    {
        return DiscountFacade::find($this->id());
    }

    public function blueprint()
    {
        return DiscountFacade::blueprint();
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'name', 'type', 'discount_code'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedDiscount($this);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'name' => $this->name(),
            'type' => $this->type(),
        ], $this->data()->toArray());
    }

    public function editUrl()
    {
        return cp_route('cargo.discounts.edit', $this->id());
    }

    public function updateUrl()
    {
        return cp_route('cargo.discounts.update', $this->id());
    }

    public function reference(): string
    {
        return "discount::{$this->id()}";
    }

    public function getQueryableValue(string $field)
    {
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
