<?php

namespace DuncanMcClean\Cargo\Query\Scopes\Filters\Fields;

use DuncanMcClean\Cargo\Facades\PaymentGateway;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class PaymentGateways extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Is'),
                    '<>' => __('Isn\'t'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'select',
                'placeholder' => __('Payment Gateway'),
                'options' => PaymentGateway::all()
                    ->mapWithKeys(fn ($gateway) => [$gateway->handle() => $gateway->title()])
                    ->all(),
                'if' => [
                    'operator' => 'contains_any <>, =',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        match ($operator) {
            'null' => $query->whereNull($handle),
            'not-null' => $query->whereNotNull($handle),
            default => $query->where($handle, $operator, $values['value']),
        };
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        if (in_array($operator, ['null', 'not-null'])) {
            return $field.' '.strtolower($translatedOperator);
        }

        return $field.' '.strtolower($translatedOperator).' '.PaymentGateway::find($values['value'])?->title();
    }
}
