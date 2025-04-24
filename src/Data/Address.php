<?php

namespace DuncanMcClean\Cargo\Data;

use Illuminate\Support\Facades\File;
use Statamic\Dictionaries\Item;
use Statamic\Facades\Dictionary;

class Address
{
    public function __construct(
        public ?string $name = null,
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $postcode = null,
        public ?string $country = null,
        public ?string $state = null,
    ) {}

    public function country(): ?Item
    {
        if (! $this->country) {
            return null;
        }

        return Dictionary::find('countries')->get($this->country);
    }

    public function state(): ?array
    {
        if (! $this->state) {
            return null;
        }

        $states = File::json(__DIR__.'/../../resources/json/states.json');

        if (! isset($states[$this->country])) {
            return null;
        }

        return array_values(array_filter($states[$this->country], fn ($state) => $state['code'] === $this->state))[0];
    }

    public function __toString(): string
    {
        return collect([
            $this->name,
            $this->line1,
            $this->line2,
            $this->city,
            $this->state() ? $this->state()['name'] : null,
            $this->postcode,
            $this->country()?->extra()['name'],
        ])->filter()->implode(', ');
    }
}
