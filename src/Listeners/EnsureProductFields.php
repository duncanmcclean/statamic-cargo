<?php

namespace DuncanMcClean\Cargo\Listeners;

use DuncanMcClean\Cargo\Cargo;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Blueprint;

class EnsureProductFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! $this->isProductBlueprint($event->blueprint)) {
            return;
        }

        if (! $event->blueprint->hasField('price') && ! $event->blueprint->hasField('product_variants')) {
            $event->blueprint->ensureField('price', [
                'type' => 'money',
                'display' => __('Price'),
                'instructions' => config('statamic.cargo.taxes.price_includes_tax')
                    ? __('cargo::messages.products.price.inclusive_of_tax')
                    : __('cargo::messages.products.price.exclusive_of_tax'),
                'listable' => 'hidden',
                'validate' => 'required',
            ], 'sidebar');
        }

        if (Cargo::usingDefaultTaxDriver() && ! $event->blueprint->hasField('tax_class')) {
            $event->blueprint->ensureField('tax_class', [
                'type' => 'tax_classes',
                'display' => __('Tax Class'),
                'instructions' => __('cargo::messages.products.tax_class'),
                'listable' => 'hidden',
                'max_items' => 1,
                'create' => true,
                'validate' => 'required',
            ], 'sidebar');
        }

        if (config('statamic.cargo.products.digital_products')) {
            $this->ensureDigitalProductFields($event);
        }
    }

    private function isProductBlueprint(Blueprint $blueprint): bool
    {
        $collections = config('statamic.cargo.products.collections');

        return in_array($blueprint->namespace(), collect($collections)->map(fn ($collection) => "collections.{$collection}")->all());
    }

    private function ensureDigitalProductFields(EntryBlueprintFound $event): void
    {
        if (! $event->blueprint->hasField('type')) {
            $event->blueprint->ensureField('type', [
                'type' => 'button_group',
                'display' => __('Product Type'),
                'instructions' => __('cargo::messages.products.type'),
                'options' => [
                    'physical' => __('Physical'),
                    'digital' => __('Digital'),
                ],
                'default' => 'physical',
                'validate' => 'required',
            ], 'sidebar');
        }

        $fields = collect([
            'downloads' => [
                'type' => 'assets',
                'display' => __('Downloads'),
                'instructions' => __('cargo::messages.products.downloads'),
                'container' => AssetContainer::all()->first()->handle(),
                'listable' => 'hidden',
                'if' => [
                    'root.type' => 'equals digital',
                ],
            ],
            'download_limit' => [
                'type' => 'integer',
                'display' => __('Download Limit'),
                'instructions' => __('cargo::messages.products.download_limit'),
                'listable' => 'hidden',
                'if' => [
                    'root.type' => 'equals digital',
                ],
            ],
        ]);

        if (
            AssetContainer::all()->isNotEmpty()
            && $event->blueprint->hasField('product_variants')
        ) {
            $productVariantsField = $event->blueprint->field('product_variants');
            $existingOptionFields = collect($productVariantsField->get('option_fields'));

            $event->blueprint->ensureFieldHasConfig('product_variants', [
                ...$productVariantsField->toArray(),
                'option_fields' => [
                    ...$productVariantsField->get('option_fields', []),
                    ...$fields
                        ->reject(fn ($field, $handle) => $existingOptionFields->keyBy('handle')->has($handle))
                        ->map(fn ($field, $handle) => [
                            'handle' => $handle,
                            'field' => $field,
                        ])
                        ->values()
                        ->toArray(),
                ],
            ]);
        } elseif (
            AssetContainer::all()->isNotEmpty()
        ) {
            $fields
                ->reject(fn ($field, $handle) => $event->blueprint->hasField($handle))
                ->each(fn ($field, $handle) => $event->blueprint->ensureField($handle, $field, 'sidebar'));
        }
    }
}
