<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone;
use DuncanMcClean\Cargo\Contracts\Taxes\TaxZoneRepository as Contract;
use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Rules\UniqueTaxZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;

class TaxZoneRepository implements Contract
{
    public function all(): Collection
    {
        if (! File::exists($this->getPath())) {
            return collect();
        }

        $parse = YAML::file($this->getPath())->parse();

        return collect($parse)->map(function ($taxZone, $handle) {
            return $this->make()->handle($handle)->data($taxZone);
        });
    }

    public function find(string $handle): ?TaxZone
    {
        return $this->all()->firstWhere('handle', $handle);
    }

    public function make(): TaxZone
    {
        return app(TaxZone::class);
    }

    public function save(TaxZone $taxZone): void
    {
        File::ensureDirectoryExists(dirname($this->getPath()));

        $data = $this->all()
            ->mapWithKeys(fn ($taxZone) => [$taxZone->handle() => $taxZone->fileData()])
            ->put($taxZone->handle(), $taxZone->fileData())
            ->all();

        $contents = YAML::dump($data);

        File::put($this->getPath(), $contents);
    }

    public function delete(string $handle): void
    {
        $data = $this->all()
            ->reject(fn ($taxZone) => $taxZone->handle() === $handle)
            ->mapWithKeys(fn ($taxZone) => [$taxZone->handle() => $taxZone->fileData()])
            ->all();

        $contents = YAML::dump($data);

        File::put($this->getPath(), $contents);
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make('tax-zone')->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => __('Tax Zone Details'),
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Name'),
                                        'validate' => 'required',
                                    ],
                                ],
                                [
                                    'handle' => 'type',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Type'),
                                        'instructions' => __('cargo::messages.tax_zones_type_instructions'),
                                        'options' => [
                                            'everywhere' => __('Everywhere'),
                                            'countries' => __('Limit to countries'),
                                            'states' => __('Limit to states'),
                                            'postcodes' => __('Limit to postcodes'),
                                        ],
                                        'default' => 'everywhere',
                                        'validate' => ['required', new UniqueTaxZone],
                                    ],
                                ],
                                [
                                    'handle' => 'countries',
                                    'field' => [
                                        'type' => 'dictionary',
                                        'display' => __('Countries'),
                                        'dictionary' => 'countries',
                                        'validate' => ['required_unless:type,everywhere'],
                                        'unless' => ['type' => 'everywhere'],
                                    ],
                                ],
                                [
                                    'handle' => 'states',
                                    'field' => [
                                        'type' => 'states',
                                        'display' => __('States'),
                                        'from' => 'countries',
                                        'validate' => [
                                            'required_if:type,states',
                                        ],
                                        'if' => ['type' => 'states'],
                                    ],
                                ],
                                [
                                    'handle' => 'postcodes',
                                    'field' => [
                                        'type' => 'list',
                                        'display' => __('Postcodes'),
                                        'instructions' => __('List each postcode on a new line. Supports wildcards like `G2*`.'),
                                        'rows' => 10,
                                        'validate' => [
                                            'required_if:type,postcodes',
                                        ],
                                        'if' => ['type' => 'postcodes'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Tax Rates'),
                            'instructions' => __('cargo::messages.tax_zones_rates_instructions'),
                            'fields' => [
                                [
                                    'handle' => 'rates',
                                    'field' => [
                                        'type' => 'group',
                                        'hide_display' => true,
                                        'fullscreen' => false,
                                        'border' => false,
                                        'fields' => TaxClass::all()->map(fn ($taxClass) => [
                                            'handle' => $taxClass->handle(),
                                            'field' => [
                                                'type' => 'integer',
                                                'display' => $taxClass->get('title'),
                                                'validate' => 'min:0',
                                                'append' => '%',
                                                'width' => 50,
                                            ],
                                        ])->values()->all(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function getPath(): string
    {
        return base_path('content/cargo/tax-zones.yaml');
    }

    public static function bindings(): array
    {
        return [
            TaxZone::class => \DuncanMcClean\Cargo\Taxes\TaxZone::class,
        ];
    }
}
