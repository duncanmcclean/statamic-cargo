<?php

namespace DuncanMcClean\Cargo;

use DuncanMcClean\Cargo\Facades\Coupon;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Facades\PaymentGateway;
use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Stache\Query\CartQueryBuilder;
use DuncanMcClean\Cargo\Stache\Query\CouponQueryBuilder;
use DuncanMcClean\Cargo\Stache\Query\OrderQueryBuilder;
use DuncanMcClean\Cargo\Stache\Stores\CartsStore;
use DuncanMcClean\Cargo\Stache\Stores\CouponsStore;
use DuncanMcClean\Cargo\Stache\Stores\OrdersStore;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Route;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Support\Str;
use Statamic\Console\Commands\Multisite as MultisiteCommand;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\File;
use Statamic\Facades\Git;
use Statamic\Facades\Permission;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $config = false;

    protected $policies = [
        Contracts\Coupons\Coupon::class => Policies\CouponPolicy::class,
        Contracts\Orders\Order::class => Policies\OrderPolicy::class,
    ];

    public $singletons = [
        Contracts\Taxes\Driver::class => Taxes\DefaultTaxDriver::class,
    ];

    protected $vite = [
        'hotFile' => __DIR__.'/../resources/dist/hot',
        'publicDirectory' => 'resources/dist',
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cargo.php', 'statamic.cargo');

        $this->publishes([
            __DIR__.'/../config/cargo.php' => config_path('statamic/cargo.php'),
        ], 'cargo-config');

        $this->publishes([
            __DIR__.'/../resources/views/checkout' => resource_path('views/checkout'),
        ], 'cargo-prebuilt-checkout');

        $this->publishes([
            __DIR__.'/../resources/views/packing-slip.antlers.html' => resource_path('views/vendor/cargo/packing-slip.antlers.html'),
        ], 'cargo-packing-slip');

        User::computed('orders', function ($user) {
            return Order::query()->where('customer', $user->getKey())->orderByDesc('date')->pluck('id')->all();
        });

        $this
            ->bootStacheStores()
            ->bootRepositories()
            ->bootNav()
            ->bootPermissions()
            ->bootRouteBindings()
            ->bootGit()
            ->registerBlueprintNamespace()
            ->addAboutCommandInfo()
            ->addMultisiteCommandHook();
    }

    protected function bootStacheStores(): self
    {
        $this->app['stache']->registerStores([
            (new CartsStore)->directory(config('statamic.cargo.carts.directory')),
            (new CouponsStore)->directory(config('statamic.cargo.coupons.directory')),
            (new OrdersStore)->directory(config('statamic.cargo.orders.directory')),
        ]);

        $this->app->bind(CartQueryBuilder::class, function () {
            return new CartQueryBuilder($this->app->make(Stache::class)->store('carts'));
        });

        $this->app->bind(CouponQueryBuilder::class, function () {
            return new CouponQueryBuilder($this->app->make(Stache::class)->store('coupons'));
        });

        $this->app->bind(OrderQueryBuilder::class, function () {
            return new OrderQueryBuilder($this->app->make(Stache::class)->store('orders'));
        });

        return $this;
    }

    protected function bootRepositories(): self
    {
        collect([
            \DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class => \DuncanMcClean\Cargo\Stache\Repositories\CartRepository::class,
            \DuncanMcClean\Cargo\Contracts\Coupons\CouponRepository::class => \DuncanMcClean\Cargo\Stache\Repositories\CouponRepository::class,
            \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class => \DuncanMcClean\Cargo\Stache\Repositories\OrderRepository::class,
            \DuncanMcClean\Cargo\Contracts\Products\ProductRepository::class => \DuncanMcClean\Cargo\Products\ProductRepository::class,
            \DuncanMcClean\Cargo\Contracts\Taxes\TaxClassRepository::class => \DuncanMcClean\Cargo\Taxes\TaxClassRepository::class,
            \DuncanMcClean\Cargo\Contracts\Taxes\TaxZoneRepository::class => \DuncanMcClean\Cargo\Taxes\TaxZoneRepository::class,
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        if (config('statamic.cargo.carts.driver') === 'eloquent') {
            $this->app->bind('cargo.carts.eloquent.model', function () {
                return config('statamic.cargo.carts.model', \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class);
            });

            $this->app->bind('cargo.carts.eloquent.line_items_model', function () {
                return config('statamic.cargo.carts.line_items_model', \DuncanMcClean\Cargo\Cart\Eloquent\LineItemModel::class);
            });

            Statamic::repository(
                \DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class,
                \DuncanMcClean\Cargo\Cart\Eloquent\CartRepository::class
            );
        }

        if (config('statamic.cargo.orders.driver') === 'eloquent') {
            $this->app->bind('cargo.orders.eloquent.model', function () {
                return config('statamic.cargo.orders.model', \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class);
            });

            $this->app->bind('cargo.orders.eloquent.line_items_model', function () {
                return config('statamic.cargo.orders.line_items_model', \DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel::class);
            });

            Statamic::repository(
                \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class,
                \DuncanMcClean\Cargo\Orders\Eloquent\OrderRepository::class
            );
        }

        return $this;
    }

    protected function bootNav(): self
    {
        Nav::extend(function ($nav) {
            $nav->create(__('Orders'))
                ->section('Store')
                ->route('cargo.orders.index')
                ->icon(Cargo::svg('shop'))
                ->can('view orders');

            $nav->create(__('Coupons'))
                ->section('Store')
                ->route('cargo.coupons.index')
                ->icon('tags')
                ->can('view coupons');

            if (Cargo::usingDefaultTaxDriver()) {
                $nav->create(__('Tax Classes'))
                    ->section('Store')
                    ->route('cargo.tax-classes.index')
                    ->icon(Cargo::svg('money-cash-file-dollar'))
                    ->can('manage taxes');

                $nav->create(__('Tax Zones'))
                    ->section('Store')
                    ->route('cargo.tax-zones.index')
                    ->icon('pin')
                    ->can('manage taxes');
            }
        });

        return $this;
    }

    protected function bootPermissions(): self
    {
        Permission::extend(function () {
            Permission::group('cargo', __('Cargo'), function () {
                Permission::register('view coupons', function ($permission) {
                    $permission->label(__('View Coupons'));

                    $permission->children([
                        Permission::make('edit coupons')->label(__('Edit Coupons'))->children([
                            Permission::make('create coupons')->label(__('Create Coupons')),
                            Permission::make('delete coupons')->label(__('Delete Coupons')),
                        ]),
                    ]);
                });

                Permission::register('view orders', function ($permission) {
                    $permission->label(__('View Orders'));

                    $permission->children([
                        Permission::make('edit orders')->label(__('Edit Orders')),
                        Permission::make('refund orders')->label(__('Refund Orders')),
                    ]);
                });

                if (Cargo::usingDefaultTaxDriver()) {
                    Permission::register('manage taxes')->label(__('Manage Taxes'));
                }
            });
        });

        return $this;
    }

    protected function bootRouteBindings(): self
    {
        Route::bind('coupon', function ($id, $route = null) {
            if (! $route || ! $this->isCpRoute($route)) {
                return false;
            }

            $field = $route->bindingFieldFor('coupon') ?? 'id';

            return $field == 'id'
                ? Coupon::find($id)
                : Coupon::query()->where($field, $id)->first();
        });

        Route::bind('order', function ($id, $route = null) {
            if (! $route || ! $this->isCpRoute($route)) {
                return false;
            }

            $field = $route->bindingFieldFor('order') ?? 'id';

            return $field == 'id'
                ? Order::find($id)
                : Order::query()->where($field, $id)->first();
        });

        Route::bind('tax-class', function ($handle, $route = null) {
            if (! $route || ! $this->isCpRoute($route)) {
                return false;
            }

            return TaxClass::find($handle);
        });

        Route::bind('tax-zone', function ($handle, $route = null) {
            if (! $route || ! $this->isCpRoute($route)) {
                return false;
            }

            return TaxZone::find($handle);
        });

        return $this;
    }

    protected function isCpRoute(\Illuminate\Routing\Route $route)
    {
        $cp = \Statamic\Support\Str::ensureRight(config('statamic.cp.route'), '/');

        if ($cp === '/') {
            return true;
        }

        return Str::startsWith($route->uri(), $cp);
    }

    protected function bootGit(): self
    {
        if (config('statamic.git.enabled')) {
            $gitEvents = [
                Events\CartDeleted::class,
                Events\CartSaved::class,
                Events\CouponDeleted::class,
                Events\CouponSaved::class,
                Events\OrderDeleted::class,
                Events\OrderSaved::class,
                Events\TaxClassDeleted::class,
                Events\TaxClassSaved::class,
                Events\TaxZoneDeleted::class,
                Events\TaxZoneSaved::class,
            ];

            foreach ($gitEvents as $event) {
                Git::listen($event);
            }
        }

        return $this;
    }

    protected function registerBlueprintNamespace(): self
    {
        Blueprint::addNamespace('cargo', __DIR__.'/../resources/blueprints');

        if (! Blueprint::find('cargo::order')) {
            Blueprint::make('order')->setNamespace('cargo')->save();
        }

        return $this;
    }

    protected function addAboutCommandInfo(): self
    {
        AboutCommand::add('Cargo', fn () => [
            'Carts' => config('statamic.cargo.carts.driver'),
            'Orders' => config('statamic.cargo.orders.driver'),
            'Payment Gateways' => collect(config('statamic.cargo.payments.gateways'))
                ->map(function (array $gateway, string $handle) {
                    $paymentGateway = PaymentGateway::find($handle);

                    if (! $paymentGateway) {
                        return $handle;
                    }

                    if (! Str::startsWith(get_class($paymentGateway), 'DuncanMcClean\\Cargo')) {
                        return "{$paymentGateway->title()} (Custom)";
                    }

                    return $paymentGateway->title();
                })
                ->filter()
                ->join(', '),
        ]);

        return $this;
    }

    protected function addMultisiteCommandHook(): self
    {
        MultisiteCommand::hook('after', function ($payload, $next) {
            Config::set('statamic.system.multisite', false);

            if (config('statamic.cargo.carts.driver') === 'file') {
                $this->components->task(
                    description: 'Updating carts',
                    task: function () {
                        $base = \Statamic\Facades\Stache::store('carts')->directory();

                        File::makeDirectory("{$base}/{$this->siteHandle}");

                        File::getFiles($base)->each(function ($file) use ($base) {
                            $filename = pathinfo($file, PATHINFO_BASENAME);
                            File::move($file, "{$base}/{$this->siteHandle}/{$filename}");
                        });
                    }
                );
            }

            if (config('statamic.cargo.orders.driver') === 'file') {
                $this->components->task(
                    description: 'Updating orders',
                    task: function () {
                        $base = \Statamic\Facades\Stache::store('orders')->directory();

                        File::makeDirectory("{$base}/{$this->siteHandle}");

                        File::getFiles($base)->each(function ($file) use ($base) {
                            $filename = pathinfo($file, PATHINFO_BASENAME);
                            File::move($file, "{$base}/{$this->siteHandle}/{$filename}");
                        });
                    }
                );
            }

            Config::set('statamic.system.multisite', true);

            return $next($payload);
        });

        return $this;
    }
}
