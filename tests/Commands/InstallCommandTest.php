<?php

namespace Tests\Commands;

use DuncanMcClean\Cargo\Support\CodeInjection;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
        $app['config']->set('statamic.cargo.taxes.tax_classes.path', $this->basePath.'/content/cargo/tax-classes.yaml');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->basePath);

        parent::tearDown();
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

    private function runInstallCommand(): void
    {
        $this->artisan('statamic:cargo:install')
            ->expectsQuestion('Which currency do you want to use?', 'GBP')
            ->expectsQuestion('Which currency do you want to use?', 'GBP')
            ->expectsQuestion('Which collection contains your products?', 'new')
            ->expectsQuestion('What should the collection be called?', 'Products')
            ->expectsConfirmation('Would you like to ignore the carts directory from Git?', 'no')
            ->expectsConfirmation('Would you like to ignore the orders directory from Git?', 'no')
            ->expectsConfirmation('Would you like to publish the pre-built checkout flow?', 'no')
            ->assertSuccessful();
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
}
