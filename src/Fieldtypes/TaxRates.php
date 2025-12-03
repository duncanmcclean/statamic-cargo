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

    public function preload()
    {
        return [
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->addValues($this->field->value() ?? $this->defaultGroupData())->meta()->toArray(),
        ];
    }
}
