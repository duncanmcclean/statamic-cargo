<?php

namespace DuncanMcClean\Cargo\Commands;

use DuncanMcClean\Cargo\Data\Currencies;
use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Support\CodeInjection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Support\Str;
use Stillat\Proteus\Support\Facades\ConfigWriter;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class InstallCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:install';

    protected $description = 'Installs Cargo.';

    public function handle(): void
    {
        $this->output->write(PHP_EOL.'<fg=#02747E;options=bold>
     ______                     
    / ____/___ __________ _____ 
   / /   / __ `/ ___/ __ `/ __ \
  / /___/ /_/ / /  / /_/ / /_/ /
  \____/\__,_/_/   \__, /\____/ 
                  /____/        
                </>'.PHP_EOL);

        if (! $this->input->isInteractive()) {
            $this->components->warn('Please run this command interactively.');

            return;
        }

        $this
            ->publishConfig()
            ->configureSiteCurrencies()
            ->configureProductsCollection()
            ->promptToGitignoreCartsDirectory()
            ->promptToGitignoreOrdersDirectory()
            ->publishMailables()
            ->publishPrebuiltCheckout()
            ->schedulePurgeAbandonedCartsCommand()
            ->createGeneralTaxClass();

        $this->line('  <fg=green;options=bold>Cargo has been installed successfully!</> 🎉');
        $this->newLine();
        $this->line('  For more information on getting started, please review the documentation: <comment>https://builtwithcargo.dev</comment>');
        $this->newLine();
    }

    private function publishConfig(): self
    {
        $this->call('vendor:publish', [
            '--tag' => 'cargo-config',
            '--force' => true,
        ]);

        $this->components->info('Published [config/statamic/cargo.php] config file.');

        return $this;
    }

    private function configureSiteCurrencies(): self
    {
        $sites = Site::all()->map(function ($site) {
            $label = Site::hasMultiple()
                ? "Which currency do you want to use for [{$site->name()}]?"
                : 'Which currency do you want to use?';

            $currency = search(
                label: $label,
                options: fn (string $value) => Currencies::mapWithKeys(fn ($currency) => [$currency['code'] => "{$currency['name']} ({$currency['code']})"])
                    ->when(strlen($value) > 0, fn ($currencies) => $currencies->filter(fn ($name) => Str::contains($name, $value, ignoreCase: true)))
                    ->when(strlen($value) === 0 && $site->attribute('currency'), fn ($currencies) => $currencies->sortBy(fn ($name, $code) => $code === $site->attribute('currency') ? 0 : 1))
                    ->all(),

            );

            return array_merge($site->rawConfig(), [
                'attributes' => array_merge($site->attributes(), ['currency' => $currency]),
            ]);
        });

        Site::setSites($sites->all())->save();

        return $this;
    }

    private function configureProductsCollection(): self
    {
        $collection = select(
            label: 'Which collection contains your products?',
            options: Collection::all()
                ->mapWithKeys(fn ($collection) => [$collection->handle() => $collection->title()])
                ->merge(['new' => 'Create a "Products" collection'])
                ->all(),
            hint: 'If you need to, you can always add additional collections later.'
        );

        if ($collection === 'new') {
            $name = text('What should the collection be called?', default: 'Products');

            Collection::make($collection = Str::kebab($name))
                ->title($name)
                ->routes([Site::default()->handle() => Str::plural(Str::kebab($name)).'/{slug}'])
                ->save();

            $this->components->info("Collection [{$name}] created.");
        }

        ConfigWriter::write('statamic.cargo.products.collections', [$collection]);

        return $this;
    }

    private function promptToGitignoreCartsDirectory(): self
    {
        if (confirm('Would you like to ignore the carts directory from Git?')) {
            File::ensureDirectoryExists(config('statamic.cargo.carts.directory'));
            File::put(config('statamic.cargo.carts.directory').'/.gitignore', "*\n!.gitignore");
        }

        return $this;
    }

    private function promptToGitignoreOrdersDirectory(): self
    {
        if (confirm('Would you like to ignore the orders directory from Git?', default: false)) {
            File::ensureDirectoryExists(config('statamic.cargo.orders.directory'));
            File::put(config('statamic.cargo.orders.directory').'/.gitignore', "*\n!.gitignore");
        }

        return $this;
    }

    private function publishMailables(): self
    {
        $mailablePath = app_path('Mail/OrderConfirmation.php');
        $mailableViewPath = resource_path('views/emails/order-confirmation.blade.php');

        if (File::exists($mailablePath) && File::exists($mailableViewPath)) {
            return $this;
        }

        File::ensureDirectoryExists(app_path('Mail'));
        File::put($mailablePath, File::get(__DIR__.'/stubs/install/OrderConfirmation.php.stub'));

        File::ensureDirectoryExists(resource_path('views/emails'));
        File::put($mailableViewPath, File::get(__DIR__.'/stubs/install/order-confirmation.blade.php.stub'));

        CodeInjection::injectImportsIntoAppServiceProvider([
            'Illuminate\Support\Facades\Event',
            'Illuminate\Support\Facades\Mail',
            'App\Mail\OrderConfirmation',
            'DuncanMcClean\Cargo\Events\OrderPaymentReceived',
        ]);

        try {
            CodeInjection::injectIntoAppServiceProviderBoot($code = <<<'PHP'
    Event::listen(OrderPaymentReceived::class, function ($event) {
            Mail::to($event->order->customer())
                ->locale($event->order->site()->shortLocale())
                ->send(new OrderConfirmation($event->order));
        });
PHP);
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Code has already been injected.') {
                $this->components->warn('Failed to inject code into AppServiceProvider. Please add the following to the <comment>boot</comment> method manually:');
                $this->line($code);
                $this->newLine();
            }
        }

        $this->components->info("Mailables published. You'll find them in <comment>app/Mail</comment> and <comment>resources/views/emails</comment>.");

        return $this;
    }

    private function publishPrebuiltCheckout(): self
    {
        $this->line('  Cargo includes a <fg=green;options=bold>pre-built checkout flow</> - featuring a minimal design and flexible Antlers templates.');

        if (! confirm(
            label: 'Would you like to publish the pre-built checkout flow?',
            default: ! File::exists(resource_path('views/checkout')),
            yes: 'Yes, please.',
            no: 'No, I will build my own checkout page.',
            hint: 'You can always change your mind later.'
        )) {
            return $this;
        }

        $this->call('vendor:publish', [
            '--tag' => 'cargo-prebuilt-checkout',
            '--force' => true,
        ]);

        $routes = File::get(base_path('routes/web.php'));

        $routes .= <<<'PHP'

Route::statamic('checkout', 'checkout.index', ['title' => 'Checkout', 'layout' => 'checkout.layout'])
    ->name('checkout');

Route::statamic('checkout/confirmation', 'checkout.confirmation', ['title' => 'Order Confirmation', 'layout' => 'checkout.layout'])
    ->name('checkout.confirmation')
    ->middleware('signed');
PHP;

        File::put(base_path('routes/web.php'), $routes);

        $this->components->info('Registered checkout routes in [routes/web.php].');

        return $this;
    }

    private function schedulePurgeAbandonedCartsCommand(): self
    {
        $consoleRoutes = File::get(base_path('routes/console.php'));

        if (Str::contains($consoleRoutes, 'statamic:cargo:purge-abandoned-carts')) {
            return $this;
        }

        $consoleRoutes .= <<<'PHP'

Schedule::command('statamic:cargo:purge-abandoned-carts')->daily();
PHP;

        File::put(base_path('routes/console.php'), $consoleRoutes);

        $this->components->info('Command [cargo:purge-abandoned-carts] has been scheduled to run daily.');

        return $this;
    }

    private function createGeneralTaxClass(): self
    {
        TaxClass::make()->handle('general')->set('name', __('General'))->save();

        return $this;
    }
}
