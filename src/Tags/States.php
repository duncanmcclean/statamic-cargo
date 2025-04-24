<?php

namespace DuncanMcClean\Cargo\Tags;

use Statamic\Dictionaries\Countries;
use Statamic\Facades\Dictionary;
use Statamic\Tags\Tags;

class States extends Tags
{
    public function index()
    {
        $country = $this->params->get('country');

        if (! $country) {
            throw new \Exception('You must provide the "country" parameter to the states tag.');
        }

        $countryItem = Dictionary::find('countries')->get($country);

        return \DuncanMcClean\Cargo\Data\States::byCountry($country)
            ->map(fn (array $state) => [
                ...$state,
                'country' => $countryItem,
            ])
            ->sortBy('name')
            ->all();
    }
}