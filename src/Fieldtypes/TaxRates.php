<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Group as GroupFieldtype;

class TaxRates extends GroupFieldtype
{
    protected $selectable = false;

    public function fields(): Fields
    {
        $fields = TaxClass::all()->map(fn ($taxClass) => [
            'handle' => $taxClass->handle(),
            'field' => [
                'type' => 'float',
                'display' => $taxClass->get('title'),
                'validate' => 'min:0',
                'append' => '%',
                'width' => 50,
            ],
        ])->values()->all();

        return new Fields($fields, $this->field()->parent(), $this->field());
    }

    public function preProcess($data)
    {
        if (is_null($data)) {
            return $data;
        }

        return collect($data)
            ->mapWithKeys(fn (int|float|null $rate, int|string $handle) => [(string) $handle => $rate])
            ->all();
    }
}
