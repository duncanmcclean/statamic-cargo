<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use Illuminate\Support\Facades\File;
use Statamic\Fields\Fieldtype;

class StateFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        $country = $this->field()->parent()?->get($this->config('from'));

        return [
            'url' => cp_route('cargo.fieldtypes.states'),
            'states' => $this->getStates($country),
        ];
    }

    public function augment($value)
    {
        $country = $this->field->parent()?->get($this->config('from'));

        return collect($this->getStates($country))->firstWhere('code', $value);
    }

    public function getStates(string|array|null $country = null): array
    {
        if (! $country) {
            return [];
        }

        $states = File::json(__DIR__.'/../../resources/json/states.json');

        return is_array($country)
            ? array_merge(...array_map(fn ($c) => $states[$c] ?? [], $country))
            : $states[$country] ?? [];
    }
}
