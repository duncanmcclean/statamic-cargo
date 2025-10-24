<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class TaxRates extends Fieldtype
{
    protected $selectable = false;

    public function process($data)
    {
        $values = $this->fields()->addValues($data ?? [])->process()->values()->all();

        return Arr::removeNullValues($values);
    }

    public function preProcess($data)
    {
        return $this->fields()->addValues($data ?? [])->preProcess()->values()->all();
    }

    private function fields(): Fields
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

    public function rules(): array
    {
        return ['array'];
    }

    public function extraRules(): array
    {
        $rules = $this
            ->fields()
            ->addValues((array) $this->field->value())
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix'),
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return [$this->field->handle().'.'.$handle => $rules];
        })->all();
    }

    public function extraValidationAttributes(): array
    {
        return collect($this->fields()->validator()->attributes())->mapWithKeys(function ($attribute, $handle) {
            return [$this->field->handle().'.'.$handle => $attribute];
        })->all();
    }

    public function preload()
    {
        return [
            'fields' => $this->fields()->all(),
            'meta' => $this->fields()->addValues($this->field->value() ?? $this->defaultGroupData())->meta()->toArray(),
        ];
    }

    protected function defaultGroupData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        })->all();
    }

    public function preProcessValidatable($value)
    {
        return array_merge(
            $value ?? [],
            $this->fields()
                ->addValues($value ?? [])
                ->preProcessValidatables()
                ->values()
                ->all(),
        );
    }
}
