<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass as Contract;
use DuncanMcClean\Cargo\Events\TaxClassDeleted;
use DuncanMcClean\Cargo\Events\TaxClassSaved;
use DuncanMcClean\Cargo\Facades;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxClass implements Augmentable, Contract
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedData;

    public $handle;

    public function __construct()
    {
        $this->data = collect();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function save(): bool
    {
        Facades\TaxClass::save($this);

        event(new TaxClassSaved($this));

        return true;
    }

    public function delete(): bool
    {
        Facades\TaxClass::delete($this->handle());

        event(new TaxClassDeleted($this));

        return true;
    }

    public function editUrl()
    {
        return cp_route('cargo.tax-classes.edit', $this->handle());
    }

    public function updateUrl()
    {
        return cp_route('cargo.tax-classes.update', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('cargo.tax-classes.destroy', $this->handle());
    }

    public function toArray(): array
    {
        return $this->data()->merge(['handle' => $this->handle()])->all();
    }

    public function fileData(): array
    {
        return $this->data()->filter()->all();
    }

    public function augmentedArrayData()
    {
        return $this->toArray();
    }
}
