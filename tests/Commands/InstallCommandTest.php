<?php

namespace Tests\Commands;

use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Support\CodeInjection;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private const MAILABLES = [
        'OrderConfirmation' => 'order-confirmation',
        'OrderShipped' => 'order-shipped',
    ];

    private string $basePath;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->basePath = __DIR__.'/__fixtures__/install';

        File::deleteDirectory($this->basePath);
        File::copyDirectory(__DIR__.'/__fixtures__/app', $this->basePath);

        $app->useStoragePath($app->storagePath());
        $app->setBasePath($this->basePath);
        $app['config']->set('statamic.cargo.carts.directory', $this->basePath.'/content/cargo/carts');
        $app['config']->set('statamic.cargo.orders.directory', $this->basePath.'/content/cargo/orders');
        $app['config']->set('statamic.cargo.taxes.tax_classes.path', $this->basePath.'/content/cargo/tax-classes.yaml');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->basePath);

        parent::tearDown();
    }

    #[Test]
    public function it_publishes_the_config_file()
    {
        $this->runInstallCommand();

        $this->assertFileExists(config_path('statamic/cargo.php'));
    }

    #[Test]
    #[DataProvider('sitesProvider')]
    public function it_configures_site_currencies(array $sites, array $currencies)
    {
        $this->setSites($sites);

        $this->runInstallCommand(currencies: $currencies);

        foreach ($currencies as $site => $currency) {
            $this->assertEquals($currency, Site::get($site)->attribute('currency'));
        }
    }

    public static function sitesProvider(): array
    {
        return [
            'single site' => [
                ['default' => ['name' => 'Default', 'url' => '/', 'locale' => 'en_US']],
                ['default' => 'USD'],
            ],
            'multiple sites' => [
                [
                    'english' => ['name' => 'English', 'url' => '/', 'locale' => 'en_GB'],
                    'french' => ['name' => 'French', 'url' => '/fr', 'locale' => 'fr_FR'],
                ],
                ['english' => 'GBP', 'french' => 'EUR'],
            ],
        ];
    }

    #[Test]
    public function it_uses_an_existing_products_collection()
    {
        Collection::make('merchandise')->title('Merchandise')->save();

        $this->runInstallCommand(collection: 'merchandise');

        $this->assertEquals(['merchandise'], $this->publishedConfig()['products']['collections']);
    }

    #[Test]
    public function it_creates_a_new_products_collection()
    {
        $this->runInstallCommand(collection: 'new', collectionName: 'Product');

        $collection = Collection::find('product');

        $this->assertEquals('Product', $collection->title());
        $this->assertEquals('products/{slug}', $collection->route('default'));
        $this->assertEquals(['product'], $this->publishedConfig()['products']['collections']);
    }

    private function publishedConfig(): array
    {
        return require config_path('statamic/cargo.php');
    }

    #[Test]
    #[DataProvider('gitignoreProvider')]
    public function it_can_gitignore_the_carts_and_orders_directories(string $directory, string $answer, bool $expectsGitignore)
    {
        $this->runInstallCommand(gitignore: [$directory => $answer]);

        $gitignore = config("statamic.cargo.{$directory}.directory").'/.gitignore';

        $expectsGitignore
            ? $this->assertStringEqualsFile($gitignore, "*\n!.gitignore")
            : $this->assertFileDoesNotExist($gitignore);
    }

    public static function gitignoreProvider(): array
    {
        return [
            'ignore carts directory' => ['carts', 'yes', true],
            'keep carts directory' => ['carts', 'no', false],
            'ignore orders directory' => ['orders', 'yes', true],
            'keep orders directory' => ['orders', 'no', false],
        ];
    }

    #[Test]
    #[DataProvider('existingMailablesProvider')]
    public function it_publishes_missing_mailables(array $existingMailables)
    {
        foreach ($existingMailables as $mailable) {
            $this->publishCustomisedMailable($mailable);
        }

        $this->runInstallCommand();

        foreach (self::MAILABLES as $mailable => $view) {
            in_array($mailable, $existingMailables)
                ? $this->assertMailableUntouched($mailable, $view)
                : $this->assertMailablePublished($mailable, $view);
        }

        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));

        $this->assertStringContainsString('use App\Mail\OrderConfirmation;', $appServiceProvider);
        $this->assertStringContainsString('use App\Mail\OrderShipped;', $appServiceProvider);
        $this->assertStringContainsString('use DuncanMcClean\Cargo\Events\OrderPaymentReceived;', $appServiceProvider);
        $this->assertStringContainsString('use DuncanMcClean\Cargo\Events\OrderShipped as OrderShippedEvent;', $appServiceProvider);
        $this->assertEquals(1, substr_count($appServiceProvider, 'Event::listen(OrderPaymentReceived::class'));
        $this->assertEquals(1, substr_count($appServiceProvider, 'Event::listen(OrderShippedEvent::class'));
        $this->assertEquals(1, substr_count($appServiceProvider, 'new OrderConfirmation($event->order)'));
        $this->assertEquals(1, substr_count($appServiceProvider, 'new OrderShipped($event->order)'));
    }

    public static function existingMailablesProvider(): array
    {
        return [
            'fresh install' => [[]],
            'order confirmation mailable already published' => [['OrderConfirmation']],
            'both mailables already published' => [['OrderConfirmation', 'OrderShipped']],
        ];
    }

    private function publishCustomisedMailable(string $mailable): void
    {
        $view = self::MAILABLES[$mailable];

        File::ensureDirectoryExists(app_path('Mail'));
        File::put(app_path("Mail/{$mailable}.php"), "<?php // customised {$mailable} mailable");

        File::ensureDirectoryExists(resource_path('views/emails'));
        File::put(resource_path("views/emails/{$view}.blade.php"), "customised {$view} view");

        $event = match ($mailable) {
            'OrderConfirmation' => 'OrderPaymentReceived',
            'OrderShipped' => 'OrderShippedEvent',
        };

        CodeInjection::injectImports(
            file: app_path('Providers/AppServiceProvider.php'),
            imports: [
                'Illuminate\Support\Facades\Event',
                'Illuminate\Support\Facades\Mail',
                "App\Mail\\{$mailable}",
                match ($mailable) {
                    'OrderConfirmation' => 'DuncanMcClean\Cargo\Events\OrderPaymentReceived',
                    'OrderShipped' => 'DuncanMcClean\Cargo\Events\OrderShipped as OrderShippedEvent',
                },
            ],
        );

        CodeInjection::injectIntoAppServiceProviderBoot(<<<PHP
    Event::listen({$event}::class, function (\$event) {
            Mail::to(\$event->order->customer())
                ->locale(\$event->order->site()->shortLocale())
                ->send(new {$mailable}(\$event->order));
        });
PHP);
    }

    private function assertMailablePublished(string $mailable, string $view): void
    {
        $this->assertFileEquals(
            __DIR__."/../../src/Commands/stubs/install/{$mailable}.php.stub",
            app_path("Mail/{$mailable}.php"),
        );

        $this->assertFileEquals(
            __DIR__."/../../src/Commands/stubs/install/{$view}.blade.php.stub",
            resource_path("views/emails/{$view}.blade.php"),
        );
    }

    private function assertMailableUntouched(string $mailable, string $view): void
    {
        $this->assertStringEqualsFile(app_path("Mail/{$mailable}.php"), "<?php // customised {$mailable} mailable");
        $this->assertStringEqualsFile(resource_path("views/emails/{$view}.blade.php"), "customised {$view} view");
    }

    #[Test]
    public function it_publishes_the_prebuilt_checkout_flow()
    {
        $this->runInstallCommand(publishCheckout: 'yes');

        $routes = File::get(base_path('routes/web.php'));

        $this->assertFileExists(resource_path('views/checkout/index.antlers.html'));
        $this->assertStringContainsString("Route::statamic('checkout', 'checkout.index'", $routes);
        $this->assertStringContainsString("Route::statamic('checkout/confirmation', 'checkout.confirmation'", $routes);
    }

    #[Test]
    public function it_does_not_publish_the_prebuilt_checkout_flow_when_declined()
    {
        $this->runInstallCommand(publishCheckout: 'no');

        $this->assertFileDoesNotExist(resource_path('views/checkout/index.antlers.html'));
        $this->assertStringNotContainsString('checkout', File::get(base_path('routes/web.php')));
    }

    #[Test]
    public function it_schedules_the_purge_abandoned_carts_command()
    {
        $this->runInstallCommand();

        $consoleRoutes = File::get(base_path('routes/console.php'));

        $this->assertStringContainsString('use Illuminate\Support\Facades\Schedule;', $consoleRoutes);
        $this->assertStringContainsString("Schedule::command('statamic:cargo:purge-abandoned-carts')->daily();", $consoleRoutes);
    }

    #[Test]
    public function it_does_not_schedule_the_purge_abandoned_carts_command_twice()
    {
        File::append(base_path('routes/console.php'), "\nSchedule::command('statamic:cargo:purge-abandoned-carts')->hourly();\n");

        $this->runInstallCommand();

        $this->assertEquals(1, substr_count(File::get(base_path('routes/console.php')), 'statamic:cargo:purge-abandoned-carts'));
    }

    #[Test]
    public function it_creates_the_general_tax_class()
    {
        $this->runInstallCommand();

        $this->assertEquals('General', TaxClass::find('general')->get('title'));
    }

    #[Test]
    public function it_sets_the_default_order_columns()
    {
        $this->runInstallCommand();

        $this->assertEquals(
            ['order_number', 'date', 'customer', 'grand_total', 'status', 'line_items'],
            Preference::default()->get('cargo.orders.columns')
        );
    }

    #[Test]
    public function it_adds_widgets_to_the_dashboard()
    {
        $this->runInstallCommand();

        $config = File::get(config_path('statamic/cp.php'));

        foreach (['total_sales', 'total_revenue', 'new_customers', 'returning_customers', 'refunded_orders'] as $widget) {
            $this->assertStringContainsString("['type' => '{$widget}', 'width' => 25, 'days' => 7],", $config);
        }
    }

    #[Test]
    public function it_does_not_add_widgets_to_the_dashboard_twice()
    {
        File::put(config_path('statamic/cp.php'), <<<'PHP'
<?php

return [

    'widgets' => [
        ['type' => 'total_sales', 'width' => 50, 'days' => 30],
    ],

];
PHP);

        $this->runInstallCommand();

        $this->assertEquals(1, substr_count(File::get(config_path('statamic/cp.php')), "'type' => 'total_sales'"));
    }

    private function runInstallCommand(
        array $currencies = ['default' => 'GBP'],
        string $collection = 'new',
        string $collectionName = 'Products',
        array $gitignore = [],
        string $publishCheckout = 'no',
    ): void {
        $command = $this->artisan('statamic:cargo:install');

        foreach ($currencies as $site => $currency) {
            $siteName = Site::get($site)->name();

            $label = Site::hasMultiple()
                ? "Which currency do you want to use for [{$siteName}]?"
                : 'Which currency do you want to use?';

            $command
                ->expectsQuestion($label, $currency)
                ->expectsQuestion($label, $currency);
        }

        $command->expectsQuestion('Which collection contains your products?', $collection);

        if ($collection === 'new') {
            $command->expectsQuestion('What should the collection be called?', $collectionName);
        }

        $gitignore = array_merge(['carts' => 'no', 'orders' => 'no'], $gitignore);

        $command
            ->expectsConfirmation('Would you like to ignore the carts directory from Git?', $gitignore['carts'])
            ->expectsConfirmation('Would you like to ignore the orders directory from Git?', $gitignore['orders'])
            ->expectsConfirmation('Would you like to publish the pre-built checkout flow?', $publishCheckout)
            ->assertSuccessful();
    }
}
