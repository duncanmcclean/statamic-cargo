<?php

namespace DuncanMcClean\Cargo\Commands\Migration;

use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\YAML;

use function Laravel\Prompts\progress;

class MigrateDiscounts extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:discounts';

    protected $description = 'Migrates discounts from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $couponFiles = collect(File::allFiles(base_path('content/simple-commerce/coupons')))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'yaml');

        if ($couponFiles->isEmpty()) {
            $this->components->warn('No discounts found to migrate.');

            return;
        }

        progress(
            label: 'Migrating discounts',
            steps: $couponFiles,
            callback: function (SplFileInfo $file) {
                $data = YAML::parse(file_get_contents($file->getRealPath()));

                if (Discount::findByDiscountCode($data['code'])) {
                    return;
                }

                $type = match ($data['type']) {
                    'percentage' => 'percentage_off',
                    'fixed' => 'amount_off',
                };

                Discount::make()
                    ->handle($data['id'])
                    ->name($data['code'])
                    ->type($type)
                    ->data(array_filter([
                        $type => $data['value'],
                        'start_date' => $data['valid_from'] ?? null,
                        'end_date' => $data['expires_at'] ?? null,
                        'discount_code' => $data['code'] ?? null,
                        'maximum_uses' => $data['maximum_uses'] ?? null,
                        'minimum_order_value' => $data['minimum_cart_value'] ?? null,
                        'customers' => $data['customers'] ?? null,
                        'products' => $data['products'] ?? null,
                    ]))
                    ->save();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Discounts migrated successfully.');
    }
}
