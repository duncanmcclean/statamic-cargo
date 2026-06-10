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

    public function get($key, $fallback = null)
    {
        $value = $this->data->get($key, $fallback);

        if ($key === 'rates' && is_array($value)) {
            return $this->normalizeRates($value);
        }

        return $value;
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            $data = $this->data;

            if ($data->has('rates')) {
                $data = $data->put('rates', $this->normalizeRates($data->get('rates')));
            }

            return $data;
        }

        $this->data = collect($data);

        return $this;
    }

    protected function normalizeRates($rates): array
    {
        return collect($rates)
            ->mapWithKeys(fn (int|float|null $rate, int|string $handle) => [(string) $handle => $rate])
            ->all();
    }

    public function rates(): Collection
    {
        return collect($this->get('rates'))
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
