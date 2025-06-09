<?php

namespace DuncanMcClean\Cargo\Discounts;

use ArrayAccess;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount as Contract;
use DuncanMcClean\Cargo\Discounts\Types\DiscountType;
use DuncanMcClean\Cargo\Events\DiscountCreated;
use DuncanMcClean\Cargo\Events\DiscountDeleted;
use DuncanMcClean\Cargo\Events\DiscountSaved;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Facades\Discount as DiscountFacade;
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

    protected $handle;
    protected $name;
    protected $amount;
    protected $type;
    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        $this->supplements = clone $this->supplements;
    }

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        return $this
            ->fluentlyGetOrSet('handle')
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

    public function saveQuietly(): bool
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save(): bool
    {
        $isNew = is_null(DiscountFacade::find($this->handle()));

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
            $this->handle(),
        ]);
    }

    public function fileData(): array
    {
        return array_merge([
            'name' => $this->name(),
            'type' => $this->type(),
        ], $this->data->all());
    }

    public function fresh(): ?Discount
    {
        return DiscountFacade::find($this->handle());
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
        return ['handle', 'name', 'type', 'discount_code'];
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
        return cp_route('cargo.discounts.edit', $this->handle());
    }

    public function updateUrl()
    {
        return cp_route('cargo.discounts.update', $this->handle());
    }

    public function reference(): string
    {
        return "discount::{$this->handle()}";
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
