<?php

namespace DuncanMcClean\Cargo\Commands\Migration;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Blueprint;

class MigrateProducts extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:products';

    protected $description = 'Migrates products from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $collectionHandle = config('simple-commerce.content.products.collection');

        // Remove Simple Commerce's ensured fields from the product blueprint(s).
        // Cargo's fields will be ensured automatically the next time the blueprint is found.
        Collection::find($collectionHandle)
            ->entryBlueprints()
            ->each(function (Blueprint $blueprint) {
                if ($blueprint->hasField('tax_category')) {
                    $blueprint->removeField('tax_category');
                }

                if ($blueprint->hasField('product_type')) {
                    $blueprint->removeField('product_type');
                }

                if ($blueprint->hasField('is_digital_product')) {
                    $blueprint->removeField('is_digital_product');
                }

                if ($blueprint->hasField('downloadable_asset')) {
                    $blueprint->removeField('downloadable_asset');
                }

                if ($blueprint->hasField('download_limit')) {
                    $blueprint->removeField('download_limit');
                }

                if ($blueprint->hasField('product_variants')) {
                    $productVariantsField = $blueprint->field('product_variants');

                    $blueprint->ensureFieldHasConfig('product_variants', [
                        ...$productVariantsField->config(),
                        'option_fields' => collect($blueprint->field('product_variants')->get('option_fields'))
                            ->reject(fn ($optionField) => in_array($optionField['handle'], ['downloadable_asset', 'download_limit']))
                            ->values()
                            ->all(),
                    ]);
                }

                $blueprint->save();
            });

        // Update field handles in entries.
        Entry::query()
            ->where('collection', $collectionHandle)
            ->whereNotNull('tax_category')
            ->orWhereNotNull('product_type')
            ->orWhereNotNull('is_digital_product')
            ->orWhereNotNull('downloadable_asset')
            ->orWhereNotNull('download_limit')
            ->orWhereNotNull('product_variants')
            ->chunk(50, function ($entries) {
                $entries->each(function ($entry) {
                    if ($entry->has('tax_category')) {
                        $entry->set('tax_class', $entry->get('tax_category'));
                        $entry->remove('tax_category');
                    }

                    if ($entry->has('product_type')) {
                        $entry->set('type', $entry->get('product_type'));
                        $entry->remove('product_type');
                    }

                    if ($entry->has('is_digital_product')) {
                        $entry->remove('is_digital_product');
                    }

                    if ($entry->has('downloadable_asset')) {
                        $entry->set('downloads', $entry->get('downloadable_asset'));
                        $entry->remove('downloadable_asset');
                    }

                    if ($entry->has('product_variants')) {
                        $productVariants = $entry->get('product_variants');

                        $productVariants['options'] = collect($productVariants['options'])
                            ->map(function ($option) {
                                if (isset($option['downloadable_asset'])) {
                                    $option['downloads'] = $option['downloadable_asset'];
                                    unset($option['downloadable_asset']);
                                }

                                return $option;
                            })
                            ->values()
                            ->all();

                        $entry->set('product_variants', $productVariants);
                    }

                    if ($entry->isDirty()) {
                        $entry->save();
                    }
                });
            });
    }
}
