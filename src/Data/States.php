<?php

namespace DuncanMcClean\Cargo\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class States
{
    public static function byCountry(string $country): Collection
    {
        $states = (new self)->getStates();

        return collect($states[$country] ?? []);
    }

    private function getStates(): array
    {
        return File::json(__DIR__.'/../../resources/json/states.json');
    }
}
