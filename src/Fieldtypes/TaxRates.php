<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Group as GroupFieldtype;
use Statamic\Support\Str;

class TaxRates extends GroupFieldtype
{
    protected $selectable = false;

    public function fields(): Fields
    {
        $fields = TaxClass::all()->map(fn ($taxClass) => [
            'handle' => $this->prefixHandle($taxClass->handle()),
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
        $value = collect($this->field->value() ?? $this->defaultGroupData())
            ->mapWithKeys(fn (int|float|null $rate, int|string $handle) => [$this->prefixHandle($handle) => $rate])
            ->all();

        $fields = $this->fields()->addValues($value);

        return [
            'fields' => $fields->toPublishArray(),
            'meta' => $fields->meta()->toArray(),
        ];
    }

    public function preProcess($data)
    {
        return collect($data ?? $this->defaultGroupData())
            ->mapWithKeys(fn (int|float|null $rate, int|string $handle) => [$this->prefixHandle($handle) => $rate])
            ->all();
    }

    public function process($data)
    {
        return collect($data)
            ->mapWithKeys(fn (int|float|null $rate, string $handle) => [$this->unprefixHandle($handle) => $rate])
            ->all();
    }

    private function prefixHandle(int|string $handle): string
    {
        return "rate_{$handle}";
    }

    private function unprefixHandle(string $handle): string
    {
        return Str::after($handle, 'rate_');
    }
}
