<?php

namespace DuncanMcClean\Cargo\Commands;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Taxes\Eloquent\TaxZoneModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

class DatabaseTaxZones extends Command
{
    use Concerns\PublishesMigrations, RunsInPlease;

    protected $signature = 'statamic:cargo:database-tax-zones
        { --import : Whether existing data should be imported }';

    protected $description = 'Migrates tax zones to the database.';

    public function handle(): void
    {
        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Taxes\TaxZoneRepository::class,
            \DuncanMcClean\Cargo\Taxes\File\TaxZoneRepository::class
        );

        $this
            ->publishMigrations()
            ->runMigrations()
            ->importTaxZones()
            ->updateConfig();
    }

    private function publishMigrations(): self
    {
        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_tax_zones_table.php.stub',
            name: 'create_cargo_tax_zones_table.php',
            replacements: [
                'TAX_ZONES_TABLE' => config('statamic.cargo.taxes.tax_zones.table', 'cargo_tax_zones'),
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

    private function importTaxZones(): self
    {
        if (
            ! $this->input->isInteractive()
            || (! $this->option('import') && ! confirm('Would you like to import existing tax zones?'))
        ) {
            return $this;
        }

        $taxZones = Facades\TaxZone::all();

        if ($taxZones->isEmpty()) {
            $this->components->warn('Nothing to import.');

            return $this;
        }

        $progress = progress(label: 'Importing tax zones', steps: $taxZones->count());

        $progress->start();

        $taxZones->each(function (TaxZone $taxZone) use ($progress) {
            TaxZoneModel::query()->updateOrCreate(
                ['handle' => $taxZone->handle()],
                ['data' => $taxZone->fileData()]
            );

            $progress->advance();
        });

        $progress->finish();

        $this->components->info('Tax Zones imported successfully.');

        return $this;
    }

    private function updateConfig(): self
    {
        if (config('statamic.cargo.taxes.tax_zones.driver') === 'eloquent') {
            $this->components->info('Tax Zones repository is already set to `eloquent`.');

            return $this;
        }

        ConfigWriter::write('statamic.cargo.taxes.tax_zones.driver', 'eloquent');

        $this->components->info('Cargo tax zones driver set to `eloquent`.');

        return $this;
    }
}
