<?php

namespace DuncanMcClean\Cargo\Commands;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Taxes\Eloquent\TaxClassModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

class DatabaseTaxClasses extends Command
{
    use Concerns\PublishesMigrations, RunsInPlease;

    protected $signature = 'statamic:cargo:database-tax-classes
        { --import : Whether existing data should be imported }';

    protected $description = 'Migrates tax classes to the database.';

    public function handle(): void
    {
        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Taxes\TaxClassRepository::class,
            \DuncanMcClean\Cargo\Taxes\File\TaxClassRepository::class
        );

        $this
            ->publishMigrations()
            ->runMigrations()
            ->importTaxClasses()
            ->updateConfig();
    }

    private function publishMigrations(): self
    {
        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_tax_classes_table.php.stub',
            name: 'create_cargo_tax_classes_table.php',
            replacements: [
                'TAX_CLASSES_TABLE' => config('statamic.cargo.taxes.tax_classes.table', 'cargo_tax_classes'),
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

    private function importTaxClasses(): self
    {
        if (
            ! $this->input->isInteractive()
            || (! $this->option('import') && ! confirm('Would you like to import existing tax classes?'))
        ) {
            return $this;
        }

        $taxClasses = Facades\TaxClass::all();

        $progress = progress(label: 'Importing tax classes', steps: $taxClasses->count());

        $progress->start();

        $taxClasses->each(function (TaxClass $taxClass) use ($progress) {
            TaxClassModel::query()->updateOrCreate(
                ['handle' => $taxClass->handle()],
                ['data' => $taxClass->fileData()]
            );

            $progress->advance();
        });

        $progress->finish();

        $this->components->info('Tax Classes imported successfully.');

        return $this;
    }

    private function updateConfig(): self
    {
        if (config('statamic.cargo.taxes.tax_classes.driver') === 'eloquent') {
            $this->components->info('Tax Classes repository is already set to `eloquent`.');

            return $this;
        }

        ConfigWriter::write('statamic.cargo.taxes.tax_classes.driver', 'eloquent');

        $this->components->info('Cargo tax classes driver set to `eloquent`.');

        return $this;
    }
}
