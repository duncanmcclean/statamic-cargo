<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone as Contract;
use DuncanMcClean\Cargo\Events\TaxZoneDeleted;
use DuncanMcClean\Cargo\Events\TaxZoneSaved;
use DuncanMcClean\Cargo\Facades;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxZone implements Augmentable, Contract
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedData;

    public $handle;

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

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function rates(): Collection
    {
        return collect($this->get('rates'))
            ->mapWithKeys(fn ($rate, $handle) => [(string) $handle => $rate])
            ->reject(fn ($rate) => is_null($rate));
    }

    public function save(): bool
    {
        Facades\TaxZone::save($this);

        TaxZoneSaved::dispatch($this);

        return true;
    }

    public function delete(): bool
    {
        Facades\TaxZone::delete($this->handle());

        TaxZoneDeleted::dispatch($this);

        return true;
    }

    public function editUrl(): string
    {
        return cp_route('cargo.tax-zones.edit', $this->handle());
    }

    public function updateUrl(): string
    {
        return cp_route('cargo.tax-zones.update', $this->handle());
    }

    public function deleteUrl(): string
    {
        return cp_route('cargo.tax-zones.destroy', $this->handle());
    }

    public function toArray(): array
    {
        return $this->data()->merge([
            'handle' => $this->handle(),
        ])->all();
    }

    public function fileData(): array
    {
        return $this->data()->merge([
            'rates' => $this->rates()->all(),
        ])->filter()->all();
    }

    public function augmentedArrayData(): array
    {
        return $this->toArray();
    }
}
