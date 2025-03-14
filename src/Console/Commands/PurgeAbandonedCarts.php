<?php

namespace DuncanMcClean\Cargo\Console\Commands;

use DuncanMcClean\Cargo\Jobs\PurgeAbandonedCarts as PurgeAbandonedCartsJob;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class PurgeAbandonedCarts extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:purge-abandoned-carts';

    protected $description = 'Purges abandoned carts.';

    public function handle()
    {
        $this->components->info('Purging abandoned carts...');

        PurgeAbandonedCartsJob::dispatch();
    }
}
