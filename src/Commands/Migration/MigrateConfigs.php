<?php

namespace DuncanMcClean\Cargo\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\Site;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\confirm;

class MigrateConfigs extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:configs';

    protected $description = 'Migrates the Simple Commerce config file to Cargo.';

    public function handle(): void
    {
        Migrate::bindMissingFieldtypes();

        try {
            $this
                ->migrateConfigOptions()
                ->migratePaymentGatewaysConfig()
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
            $currency = config('simple-commerce.sites.'.$site->handle().'.currency') ?? $site->attribute('currency') ?? 'USD';

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

    private function migratePaymentGatewaysConfig(): self
    {
        $paymentGateways = collect(config('simple-commerce.gateways'))
            ->map(function ($value, $key): string {
                if (str_contains($key, 'DummyGateway')) {
                    return <<<'PHP'
            'dummy' => [],
PHP;
                }

                if (str_contains($key, 'StripeGateway')) {
                    return <<<'PHP'
            'stripe' => [
                 'key' => env('STRIPE_KEY'),
                 'secret' => env('STRIPE_SECRET'),
                 'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            ],
PHP;
                }

                if (str_contains($key, 'MollieGateway')) {
                    return <<<'PHP'
            'mollie' => [
                 'api_key' => env('MOLLIE_KEY'),
                 'profile_id' => env('MOLLIE_PROFILE_ID'),
            ],
PHP;
                }

                if (str_contains($key, 'PayPalGateway')) {
                    return <<<'PHP'
            // IMPORTANT: The PayPal gateway has been removed. Please visit the documentation for more information.
            // https://builtwithcargo.dev/docs/migrating-from-simple-commerce#paypal
PHP;
                }

                $handle = Str::of($key)
                    ->afterLast('\\')
                    ->replace('Gateway', '')
                    ->snake()
                    ->__toString();

                return <<<PHP
            '{$handle}' => [
                // TODO: Add your {$handle} configuration here.
            ],
PHP;
            })
            ->values()
            ->map(function ($stub, $index): string {
                // Trim the padding on the first line, of the first stub to prevent awkward indentation.
                if ($index === 0) {
                    return ltrim($stub);
                }

                return $stub;
            });

        if ($paymentGateways->isEmpty()) {
            return $this;
        }

        ConfigWriter::write('statamic.cargo.payments.gateways', ['{{PaymentGateways}}']);

        $contents = Str::of(File::get(config_path('statamic/cargo.php')))
            ->replace("'{{PaymentGateways}}',", $paymentGateways->implode(PHP_EOL.PHP_EOL))
            ->__toString();

        File::put(config_path('statamic/cargo.php'), $contents);

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
