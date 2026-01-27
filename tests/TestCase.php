<?php

namespace Tests;

use DuncanMcClean\Cargo\Discounts\DiscountServiceProvider;
use DuncanMcClean\Cargo\Orders\OrderServiceProvider;
use DuncanMcClean\Cargo\Payments\PaymentServiceProvider;
use DuncanMcClean\Cargo\ServiceProvider;
use DuncanMcClean\Cargo\Shipping\ShippingServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert;
use ReflectionClass;
use Statamic\Facades\Config;
use Statamic\Facades\Site;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventsSavingStacheItemsToDisk::class])) {
            // Old
            $reflection = new ReflectionClass($this);
            $this->fakeStacheDirectory = Str::before(dirname($reflection->getFileName()), DIRECTORY_SEPARATOR.'tests').'/tests/__fixtures__/dev-null';

            // New
//            $reflector = new ReflectionClass($this->addonServiceProvider);
//            $this->fakeStacheDirectory = dirname($reflector->getFileName()).'/../tests/__fixtures__/dev-null';

            $this->preventSavingStacheItemsToDisk();
        }

        Site::setSites([
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '/',
                'locale' => 'en_US',
                'attributes' => ['currency' => 'GBP'],
            ],
        ])->save();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.cargo', require (__DIR__.'/../config/cargo.php'));

        $app['config']->set('auth.providers.users.driver', 'statamic');
        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class' => \Statamic\Stache\Stores\UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        Route::get('checkout', fn () => 'Checkout')->name('checkout');
        Route::get('checkout/confirmation', fn () => 'Confirmation')->name('checkout.confirmation');
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            DiscountServiceProvider::class,
            OrderServiceProvider::class,
            PaymentServiceProvider::class,
            ShippingServiceProvider::class,
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/__fixtures__/migrations');
    }

    protected function setSites($sites)
    {
        Site::setSites($sites);

        Config::set('statamic.system.multisite', Site::hasMultiple());
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $reflector = new ReflectionClass($this->addonServiceProvider);
        $directory = dirname($reflector->getFileName());

        $app['config']->set('statamic.cargo.carts.directory', $directory.'/../tests/__fixtures__/content/cargo/carts');
        $app['config']->set('statamic.cargo.discounts.directory', $directory.'/../tests/__fixtures__/content/cargo/discounts');
        $app['config']->set('statamic.cargo.orders.directory', $directory.'/../tests/__fixtures__/content/cargo/orders');
    }

    public function __call($name, $arguments)
    {
        if ($name == 'assertStringEqualsStringIgnoringLineEndings') {
            return Assert::assertThat(
                $arguments[1],
                new StringEqualsStringIgnoringLineEndings($arguments[0]),
                $arguments[2] ?? ''
            );
        }

        throw new \BadMethodCallException("Method [$name] does not exist.");
    }
}
