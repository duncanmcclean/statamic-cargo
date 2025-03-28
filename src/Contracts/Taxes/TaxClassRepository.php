<?php

namespace DuncanMcClean\Cargo\Contracts\Taxes;

use Illuminate\Support\Collection;
use Statamic\Fields\Blueprint;

interface TaxClassRepository
{
    public function all(): Collection;

    public function find(string $handle): ?TaxClass;

    public function make(): TaxClass;

    public function save(TaxClass $taxClass): void;

    public function delete(string $handle): void;

    public function blueprint(): Blueprint;
}
