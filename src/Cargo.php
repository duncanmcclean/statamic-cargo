<?php

namespace DuncanMcClean\Cargo;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Addon;

class Cargo
{
    public static function version(): string
    {
        if (app()->environment('testing')) {
            return 'v1.0.0';
        }

        return Addon::get('duncanmcclean/statamic-cargo')->version();
    }

    public static function svg($name): ?string
    {
        return File::get(__DIR__.'/../resources/svg/'.$name.'.svg');
    }

    //

    public static function usingDefaultTaxDriver(): bool
    {
        return app()->bound(Contracts\Taxes\Driver::class) && app(Contracts\Taxes\Driver::class) instanceof Taxes\DefaultTaxDriver;
    }
}
