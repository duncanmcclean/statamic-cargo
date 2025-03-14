<?php

namespace DuncanMcClean\Cargo\Jobs;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

class PurgeAbandonedCarts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        Cart::query()
            ->where('updated_at', '<', Carbon::now()->subDays(config('statamic.cargo.carts.purge_abandoned_carts_after'))->timestamp)
            ->chunk(100, fn ($carts) => $carts->each->delete());
    }
}
