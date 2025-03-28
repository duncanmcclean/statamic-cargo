<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use DuncanMcClean\Cargo\Contracts\Taxes\TaxClassRepository as Contract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;

class TaxClassRepository implements Contract
{
    public function all(): Collection
    {
        if (! File::exists($this->getPath())) {
            return collect();
        }

        $parse = YAML::file($this->getPath())->parse();

        return collect($parse)->map(function ($taxClass, $handle) {
            return $this->make()->handle($handle)->data($taxClass);
        });
    }

    public function find(string $handle): ?TaxClass
    {
        return $this->all()->firstWhere('handle', $handle);
    }

    public function make(): TaxClass
    {
        return app(TaxClass::class);
    }

    public function save(TaxClass $taxClass): void
    {
        File::ensureDirectoryExists(dirname($this->getPath()));

        $data = $this->all()
            ->mapWithKeys(fn ($taxClass) => [$taxClass->handle() => $taxClass->fileData()])
            ->put($taxClass->handle(), $taxClass->fileData())
            ->all();

        $contents = YAML::dump($data);

        File::put($this->getPath(), $contents);
    }

    public function delete(string $handle): void
    {
        $data = $this->all()
            ->reject(fn ($taxClass) => $taxClass->handle() === $handle)
            ->mapWithKeys(fn ($taxClass) => [$taxClass->handle() => $taxClass->fileData()])
            ->all();

        $contents = YAML::dump($data);

        File::put($this->getPath(), $contents);
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make('tax-class')->setContents([
            'sections' => [
                'main' => [
                    'display' => 'Main',
                    'fields' => [
                        [
                            'handle' => 'name',
                            'field' => [
                                'type' => 'text',
                                'display' => __('Name'),
                                'instructions' => __('cargo::messages.tax_classes_name_instructions'),
                                'validate' => 'required',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function getPath(): string
    {
        return base_path('content/cargo/tax-classes.yaml');
    }

    public static function bindings(): array
    {
        return [
            TaxClass::class => \DuncanMcClean\Cargo\Taxes\TaxClass::class,
        ];
    }
}
