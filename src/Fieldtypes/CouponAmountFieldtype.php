<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class CouponAmountFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return [
            'meta' => [
                'money' => $this->resolveValueField('fixed')->fieldtype()->preload(),
                'integer' => $this->resolveValueField('percentage')->fieldtype()->preload(),
            ],
            'config' => [
                'money' => $this->resolveValueField('fixed')->config(),
                'integer' => $this->resolveValueField('percentage')->config(),
            ],
        ];
    }

    public function preProcess($data)
    {
        if (! $data) {
            return null;
        }

        return [
            'mode' => $this->field->parent()->type()->value,
            'value' => $this
                ->resolveValueField($this->field->parent()->type()->value)
                ->fieldtype()
                ->preProcess($data),
        ];
    }

    public function process($data)
    {
        return $this
            ->resolveValueField(Arr::get($data, 'mode'))
            ->fieldtype()
            ->process(Arr::get($data, 'value'));
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return $this
            ->resolveValueField($this->field->parent()->type()->value)
            ->fieldtype()
            ->augment($value);
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return null;
        }

        return $this
            ->resolveValueField($this->field->parent()->type()->value)
            ->fieldtype()
            ->preProcessIndex($data);
    }

    protected function resolveValueField(string $mode): ?Field
    {
        if ($mode === 'fixed') {
            return new Field('coupon_value', [
                'type' => 'money',
                'save_zero_value' => true,
            ]);
        }

        if ($mode === 'percentage') {
            return new Field('coupon_value', [
                'append' => '%',
                'type' => 'integer',
            ]);
        }

        return null;
    }
}
