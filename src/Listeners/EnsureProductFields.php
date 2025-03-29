<?php

namespace DuncanMcClean\Cargo\Listeners;

use DuncanMcClean\Cargo\Cargo;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Fields\Blueprint;

class EnsureProductFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! $this->isProductBlueprint($event->blueprint)) {
            return;
        }

        if (config('statamic.cargo.products.digital_products') && ! $event->blueprint->hasField('type')) {
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
    }

    private function isProductBlueprint(Blueprint $blueprint): bool
    {
        $collections = config('statamic.cargo.products.collections');

        return in_array($blueprint->namespace(), collect($collections)->map(fn ($collection) => "collections.{$collection}")->all());
    }
}
