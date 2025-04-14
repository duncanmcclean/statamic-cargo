<?php

namespace DuncanMcClean\Cargo\Console\Commands\Migration;

use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Stillat\Proteus\Support\Facades\ConfigWriter;
use function Laravel\Prompts\confirm;

class MigrateConfigs extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:configs';

    protected $description = 'Migrates the Simple Commerce config file to Cargo.';

    public function handle(): void
    {
        try {
            $this
                ->migrateConfigOptions()
                ->migrateTaxes()
                ->migratePermissions();
        } catch (MigrationCancelled $e) {
            return;
        }
    }

    private function migrateConfigOptions(): self
    {
        $path = config_path('simple-commerce.php');

        while (! File::exists($path)) {
            $this->components->warn('Please keep the [config/simple-commerce.php] file for the time being.');

            if (! confirm('Do you want to continue?')) {
                throw new MigrationCancelled;
            }
        }

        // Site currencies
        $sites = Site::all()->map(function ($site) {
            $currency = config('simple-commerce.sites.' . $site->handle() . '.currency') ?? $site->attribute('currency') ?? 'USD';

            return array_merge($site->rawConfig(), [
                'attributes' => array_merge($site->attributes(), ['currency' => $currency]),
            ]);
        });

        Site::setSites($sites->all())->save();

        // Config options
        $hasDigitalProducts = Entry::query()
            ->where('collection', config('simple-commerce.content.products.collection'))
            ->where('product_type', 'digital')
            ->count() > 0;

        $config = [
            'products.collections' => [config('simple-commerce.content.products.collection')],
            'products.low_stock_threshold' => config('simple-commerce.low_stock_threshold'),
            'products.digital_products' => $hasDigitalProducts,
            'carts.cookie_name' => config('simple-commerce.cart.key', 'cargo-cart'),
            'carts.unique_metadata' => config('simple-commerce.cart.unique_metadata', false),
        ];

        ConfigWriter::writeMany('statamic.cargo', $config);

        $this->components->info('Updated the [statamic/cargo.php] config file.');

        return $this;
    }

    private function migrateTaxes(): self
    {
        $taxEngine = config('simple-commerce.tax_engine');

        if ($taxEngine === 'DuncanMcClean\SimpleCommerce\Tax\BasicTaxEngine') {
            if (! TaxClass::find('general')) {
                TaxClass::make()->handle('general')->set('name', 'General')->save();
            }

            if (! TaxZone::find('international')) {
                TaxZone::make()
                    ->handle('international')
                    ->set('name', 'International')
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
            collect(File::allFiles(base_path('content/simple-commerce/tax-categories')))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'yaml')
                ->each(function (SplFileInfo $file) {
                    $data = YAML::parse(File::get($file->getPathname()));

                    if (TaxClass::find($data['id'])) {
                        return;
                    }

                    TaxClass::make()
                        ->handle($data['id'])
                        ->set('name', $data['name'])
                        ->set('description', $data['description'] ?? '')
                        ->save();
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
                        ->set('name', $data['name'])
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
            $this->components->warn('Cargo includes tax in prices by default. If you want to change this, please update the [statamic/cargo.php] config file.');
        }

        return $this;
    }

    private function migratePermissions(): self
    {
        Role::all()->each(function ($role) {
            $permissions = $role->permissions()
                ->map(function ($permission) {
                    $mappings = [
                        'view coupons' => 'view discounts',
                        'edit coupons' => 'edit discounts',
                        'create coupons' => 'create discounts',
                        'delete coupons' => 'delete discounts',
                        'view orders entries' => 'view orders',
                        'edit orders entries' => 'edit orders',
                        'view tax rates' => 'manage taxes',
                        'edit tax rates' => 'manage taxes',
                        'create tax rates' => 'manage taxes',
                        'delete tax rates' => 'manage taxes',
                        'view tax categories' => 'manage taxes',
                        'edit tax categories' => 'manage taxes',
                        'create tax categories' => 'manage taxes',
                        'delete tax categories' => 'manage taxes',
                        'view tax zones' => 'manage taxes',
                        'edit tax zones' => 'manage taxes',
                        'create tax zones' => 'manage taxes',
                        'delete tax zones' => 'manage taxes',
                    ];

                    return $mappings[$permission] ?? $permission;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (in_array('edit orders', $permissions)) {
                $permissions[] = 'refund orders';
            }

            $role->permissions($permissions)->save();
        });

        $this->components->info('Migrated permissions.');

        return $this;
    }
}