<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Data\Address as AddressData;
use Statamic\Fields\Fields;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Group as GroupFieldtype;
use Statamic\Support\Traits\Hookable;

class Address extends GroupFieldtype
{
    use Hookable;

    protected $selectable = false;

    public function preload()
    {
        return [
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->addValues($this->normalizeValue($this->field->value()))->meta()->toArray(),
        ];
    }

    public function augment($value)
    {
        return $this->performAugmentation($value, shallow: false);
    }

    public function shallowAugment($value)
    {
        return $this->performAugmentation($value, shallow: true);
    }

    private function performAugmentation($value, bool $shallow)
    {
        $value = $this->normalizeValue($value);
        $method = $shallow ? 'shallowAugment' : 'augment';

        // The States fieldtype needs to get the "country"
        // key from the address during augmentation.
        $this->field()->setValue($value);

        return new Values($this->fields()->addValues($value ?? [])->{$method}()->values()->all());
    }

    public function fields(): Fields
    {
        $fields = [
            [
                'handle' => 'name',
                'field' => [
                    'type' => 'text',
                    'display' => __('Name'),
                    'listable' => false,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'line_1',
                'field' => [
                    'type' => 'text',
                    'display' => __('Address Line 1'),
                    'listable' => false,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'line_2',
                'field' => [
                    'type' => 'text',
                    'display' => __('Address Line 2'),
                    'listable' => false,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'city',
                'field' => [
                    'type' => 'text',
                    'display' => __('Town/City'),
                    'listable' => false,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'postcode',
                'field' => [
                    'type' => 'text',
                    'display' => __('Postcode'),
                    'listable' => false,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'country',
                'field' => [
                    'type' => 'dictionary',
                    'dictionary' => ['type' => 'countries', 'emojis' => false],
                    'max_items' => 1,
                    'display' => __('Country'),
                    'listable' => false,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'state',
                'field' => [
                    'type' => 'states',
                    'from' => 'country',
                    'display' => __('State/County'),
                    'listable' => false,
                    'max_items' => 1,
                    'width' => 50,
                ],
            ],
        ];

        $fields = $this->runHooksWith('fields', ['fields' => $fields])?->fields;

        return new Fields($fields, $this->field()->parent(), $this->field());
    }

    private function normalizeValue($value): array
    {
        if ($value instanceof AddressData) {
            return $value->toArray();
        }

        return $value ?? $this->defaultGroupData();
    }
}
