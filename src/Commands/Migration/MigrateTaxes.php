<?php

namespace DuncanMcClean\Cargo\Commands\Migration;

use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class MigrateTaxes extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:taxes';

    protected $description = 'Migrates taxes from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $taxEngine = config('simple-commerce.tax_engine');

        if ($taxEngine === 'DuncanMcClean\SimpleCommerce\Tax\BasicTaxEngine') {
            if (! TaxClass::find('general')) {
                TaxClass::make()->handle('general')->set('title', 'General')->save();
            }

            if (! TaxZone::find('international')) {
                TaxZone::make()
                    ->handle('international')
                    ->set('title', 'International')
                    ->set('type', 'everywhere')
                    ->set('rates', [
                        'general' => config('simple-commerce.tax_engine_config.rate', 20),
                    ])
                    ->save();
            }

            ConfigWriter::write(
                'statamic.cargo.taxes.price_includes_tax',
                config('simple-commerce.tax_engine_config.included_in_prices', true)
            );

            $this->components->info('Migrated tax configuration.');
        }

        if ($taxEngine === 'DuncanMcClean\SimpleCommerce\Tax\Standard\TaxEngine') {
            Migrate::bindMissingFieldtypes();

            collect(File::allFiles(base_path('content/simple-commerce/tax-categories')))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'yaml')
                ->each(function (SplFileInfo $file) {
                    $data = YAML::parse(File::get($file->getPathname()));

                    if (TaxClass::find($data['id'])) {
                        return;
                    }

                    TaxClass::make()
                        ->handle($data['id'])
                        ->set('title', $data['name'])
                        ->set('description', $data['description'] ?? '')
                        ->save();

                    Entry::query()
                        ->whereIn('collection', config('statamic.cargo.products.collections'))
                        ->where('tax_category', $data['id'])
                        ->chunk(100, function ($entries) {
                            $entries->each(function ($entry) {
                                $entry
                                    ->set('tax_class', $entry->get('tax_category'))
                                    ->remove('tax_category')
                                    ->save();
                            });
                        });
                });

            collect(File::allFiles(base_path('content/simple-commerce/tax-zones')))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'yaml')
                ->each(function (SplFileInfo $file) {
                    $data = YAML::parse(File::get($file->getPathname()));

                    if (TaxZone::find($data['id'])) {
                        return;
                    }

                    $type = match (true) {
                        isset($data['region']) => 'states',
                        isset($data['country']) => 'countries',
                        default => 'everywhere',
                    };

                    TaxZone::make()
                        ->handle($data['id'])
                        ->set('title', $data['name'])
                        ->set('type', $type)
                        ->set('countries', isset($data['country']) ? [$data['country']] : null)
                        ->set('states', isset($data['region']) ? [$data['region']] : null)
                        ->save();
                });

            collect(File::allFiles(base_path('content/simple-commerce/tax-rates')))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'yaml')
                ->each(function (SplFileInfo $file) {
                    $data = YAML::parse(File::get($file->getPathname()));

                    $taxZone = TaxZone::find($data['zone']);

                    if (! $taxZone) {
                        return;
                    }

                    $taxZone->set('rates', [
                        ...$taxZone->get('rates', []),
                        $data['category'] => (int) $data['rate'],
                    ])->save();
                });

            $this->components->info('Migrated tax configuration.');
            $this->components->warn('Cargo includes tax in prices by default. If you want to change this, please update the [config/statamic/cargo.php] config file.');
        }
    }
}
