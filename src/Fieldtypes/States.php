<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Data\States as StatesData;
use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class States extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        $country = $this->field()->parent()?->get($this->config('from'));

        if (is_array($country)) {
            $country = Arr::first($country);
        }

        return [
            'url' => cp_route('cargo.fieldtypes.states'),
            'states' => $country ? StatesData::byCountry($country) : [],
        ];
    }

    public function augment($value)
    {
        $country = $this->field->parent()?->get($this->config('from'));

        if (! $country) {
            return null;
        }

        return StatesData::byCountry($country)->firstWhere('code', $value);
    }
}
