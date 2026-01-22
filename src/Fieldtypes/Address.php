<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Data;
use DuncanMcClean\Cargo\Data\Address as AddressData;
use Statamic\Fields\Fields;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Group as GroupFieldtype;

class Address extends GroupFieldtype
{
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
        return Data\Address::blueprint()
            ->fields()
            ->setParent($this->field()->parent())
            ->setParentField($this->field());
    }

    private function normalizeValue($value): array
    {
        if ($value instanceof AddressData) {
            return $value->toArray();
        }

        return $value ?? $this->defaultGroupData();
    }
}
