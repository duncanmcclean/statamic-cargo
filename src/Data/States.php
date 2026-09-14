<?php

namespace DuncanMcClean\Cargo\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class States
{
    public static function byCountry(?string $country = null): Collection
    {
        if (! $country) {
            return collect([]);
        }

        $states = (new self)->getStates();

        return collect($states[$country] ?? [])
            ->map(function (array $state) use ($country) {
                $key = "cargo::states.{$country}.{$state['code']}";

                return [
                    ...$state,
                    'name' => Lang::has($key) ? __($key) : $state['name'],
                ];
            })
            ->sortBy('name')
            ->values();
    }

    private function getStates(): array
    {
        return File::json(__DIR__.'/../../resources/json/states.json');
    }
}
