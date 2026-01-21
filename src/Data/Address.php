<?php

namespace DuncanMcClean\Cargo\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\File;
use Statamic\Dictionaries\Item;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Dictionary;
use Statamic\Fields\Field;
use Statamic\Support\Arr;
use Statamic\Support\Traits\Hookable;
use Stringable;

class Address implements Arrayable, Stringable
{
    use Hookable;

    public function __construct(protected array $address = []) {}

    public static function make(array $address)
    {
        return new static($address);
    }

    public function country(): ?Item
    {
        $country = Arr::get($this->address, 'country');

        if (! $country) {
            return null;
        }

        return Dictionary::find('countries')->get($country);
    }

    public function state(): ?array
    {
        $state = Arr::get($this->address, 'state');
        $country = Arr::get($this->address, 'country');

        if (! $country || ! $state) {
            return null;
        }

        $states = File::json(__DIR__.'/../../resources/json/states.json');

        if (! isset($states[$country])) {
            return null;
        }

        return collect($states[$country])->firstWhere('code', $state);
    }

    public function toArray(): array
    {
        return $this->address;
    }

    public function __toString(): string
    {
        return static::blueprint()->fields()->all()
            ->map(function (Field $field) {
                $value = Arr::get($this->address, $field->handle());

                if ($field->handle() === 'state') {
                    return $this->state()['name'] ?? null;
                }

                if ($field->handle() === 'country') {
                    return $this->country()?->extra()['name'];
                }

                return $value;
            })
            ->filter()
            ->implode(', ');
    }

    public function __get(string $name)
    {
        return $this->address[$name] ?? null;
    }

    public static function blueprint(): \Statamic\Fields\Blueprint
    {
        $fields = [
            'name' => [
                'type' => 'text',
                'display' => __('Name'),
                'listable' => false,
                'width' => 50,
            ],
            'line_1' => [
                'type' => 'text',
                'display' => __('Address Line 1'),
                'listable' => false,
                'width' => 50,
            ],
            'line_2' => [
                'type' => 'text',
                'display' => __('Address Line 2'),
                'listable' => false,
                'width' => 50,
            ],
            'city' => [
                'type' => 'text',
                'display' => __('Town/City'),
                'listable' => false,
                'width' => 50,
            ],
            'postcode' => [
                'type' => 'text',
                'display' => __('Postcode'),
                'listable' => false,
                'width' => 50,
            ],
            'country' => [
                'type' => 'dictionary',
                'dictionary' => ['type' => 'countries', 'emojis' => false],
                'max_items' => 1,
                'display' => __('Country'),
                'listable' => false,
                'width' => 50,
            ],
            'state' => [
                'type' => 'states',
                'from' => 'country',
                'display' => __('State/County'),
                'listable' => false,
                'max_items' => 1,
                'width' => 50,
            ],
        ];

        $fields = (new self)->runHooksWith('fields', ['fields' => $fields])?->fields;

        return Blueprint::makeFromFields($fields);
    }
}
