<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use Illuminate\Support\Facades\File;
use Statamic\Fields\Fieldtype;
use DuncanMcClean\Cargo\Data\States as StatesData;

class States extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        $country = $this->field()->parent()?->get($this->config('from'));

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
