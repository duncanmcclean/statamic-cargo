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
        return [
            'url' => cp_route('cargo.fieldtypes.states'),
            'states' => StatesData::byCountry($this->country()),
            'selectedOptions' => $this->getItemData($this->field->value()),
        ];
    }

    public function getStates(string $country)
    {
        return StatesData::byCountry($country);
    }

    private function getItemData($values)
    {
        return collect($values)->map(function ($key) {
            $item = StatesData::byCountry($this->country())->firstWhere('code', $key);

            return [
                'value' => $item['code'] ?? $key,
                'label' => $item['name'] ?? $key,
                'invalid' => ! $item,
            ];
        })->values()->all();
    }

    public function augment($value)
    {
        return StatesData::byCountry($this->country())->firstWhere('code', $value);
    }

    private function country(): ?string
    {
        // Inside the Address fieldtype, we want to get the parent field's value
        // and extract the country from there. Otherwise, we want to get the
        // country from the parent item (eg. in tax zones).
        if ($parentField = $this->field()->parentField()) {
            $address = $parentField->value();
            $country = Arr::get($address, 'country');
        } else {
            $country = $this->field()->parent()?->get($this->config('from'));
        }

        if (is_array($country)) {
            $country = Arr::first($country);
        }

        return $country;
    }
}
