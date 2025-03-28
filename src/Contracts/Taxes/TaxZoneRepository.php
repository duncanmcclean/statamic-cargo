<?php

namespace DuncanMcClean\Cargo\Contracts\Taxes;

use Illuminate\Support\Collection;
use Statamic\Fields\Blueprint;

interface TaxZoneRepository
{
    public function all(): Collection;

    public function find(string $handle): ?TaxZone;

    public function make(): TaxZone;

    public function save(TaxZone $taxZone): void;

    public function delete(string $handle): void;

    public function blueprint(): Blueprint;
}
