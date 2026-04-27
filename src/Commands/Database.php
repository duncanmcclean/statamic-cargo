<?php

namespace DuncanMcClean\Cargo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Statamic\Console\RunsInPlease;

use function Laravel\Prompts\multiselect;

class Database extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:database
        { --all : Migrates all repositories to the database }
        { --import : Whether existing data should be imported }';

    protected $description = 'Migrates Cargo repositories to the database.';

    public function handle(): void
    {
        $repositories = $this->repositories();

        foreach ($repositories as $repository) {
            $command = 'statamic:cargo:database-'.str_replace('_', '-', $repository);

            $this->call($command, [
                '--import' => $this->option('import'),
            ]);

            $this->newLine();
        }
    }

    private function repositories(): array
    {
        if ($this->option('all')) {
            return $this->allRepositories()->keys()->all();
        }

        return multiselect(
            label: 'Which repositories would you like to migrate?',
            options: $this->allRepositories()
                ->reject(fn ($value, $key) => $this->repositoryHasBeenMigrated($key))
                ->all(),
            validate: fn (array $values) => count($values) === 0
                ? 'You must select at least one repository.'
                : null,
            hint: 'You can always migrate other repositories later.'
        );
    }

    private function allRepositories(): Collection
    {
        return collect([
            'carts' => 'Carts',
            'discounts' => 'Discounts',
            'orders' => 'Orders',
            'tax_classes' => 'Tax Classes',
            'tax_zones' => 'Tax Zones',
        ]);
    }

    private function repositoryHasBeenMigrated(string $repository): bool
    {
        return match ($repository) {
            'carts' => config('statamic.cargo.carts.driver') === 'eloquent',
            'discounts' => config('statamic.cargo.discounts.driver') === 'eloquent',
            'orders' => config('statamic.cargo.orders.driver') === 'eloquent',
            'tax_classes' => config('statamic.cargo.taxes.tax_classes.driver') === 'eloquent',
            'tax_zones' => config('statamic.cargo.taxes.tax_zones.driver') === 'eloquent',
            default => false,
        };
    }
}
