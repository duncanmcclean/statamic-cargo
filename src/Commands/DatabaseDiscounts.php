<?php

namespace DuncanMcClean\Cargo\Commands;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

class DatabaseDiscounts extends Command
{
    use Concerns\PublishesMigrations, RunsInPlease;

    protected $signature = 'statamic:cargo:database-discounts
        { --import : Whether existing data should be imported }';

    protected $description = 'Migrates discounts to the database.';

    public function handle(): void
    {
        app()->bind('cargo.discounts.eloquent.model', function () {
            return \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountModel::class;
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository::class,
            \DuncanMcClean\Cargo\Stache\Repositories\DiscountRepository::class
        );

        $this
            ->publishMigrations()
            ->runMigrations()
            ->importDiscounts()
            ->updateConfig();
    }

    private function publishMigrations(): self
    {
        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_discounts_table.php.stub',
            name: 'create_cargo_discounts_table.php',
            replacements: [
                'DISCOUNTS_TABLE' => config('statamic.cargo.discounts.table', 'cargo_discounts'),
            ]
        );

        return $this;
    }

    private function runMigrations(): self
    {
        Artisan::call('migrate', ['--force' => true], $this->output);

        $this->newLine();

        return $this;
    }

    private function importDiscounts(): self
    {
        if (
            ! $this->input->isInteractive()
            || (! $this->option('import') && ! confirm('Would you like to import existing discounts?'))
        ) {
            return $this;
        }

        $query = Discount::query();

        $progress = progress(label: 'Importing discounts', steps: $query->count());

        $progress->start();

        $query->chunk(50, function (Collection $discounts) use ($progress) {
            $discounts->each(function (DiscountContract $discount) use ($progress) {
                app('cargo.discounts.eloquent.model')::updateOrCreate(
                    ['handle' => $discount->handle()],
                    [
                        'title' => $discount->title(),
                        'type' => $discount->type(),
                        'data' => $discount->data()->except('id')->all(),
                    ]
                );

                $progress->advance();
            });
        });

        $progress->finish();

        $this->components->info('Discounts imported successfully.');

        return $this;
    }

    private function updateConfig(): self
    {
        if (config('statamic.cargo.discounts.driver') === 'eloquent') {
            $this->components->info('Discounts repository is already set to `eloquent`.');

            return $this;
        }

        ConfigWriter::write('statamic.cargo.discounts.driver', 'eloquent');

        $this->components->info('Cargo discounts driver set to `eloquent`.');

        return $this;
    }
}
