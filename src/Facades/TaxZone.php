<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZoneRepository as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Taxes\TaxZone find(string $handle)
 * @method static \DuncanMcClean\Cargo\Contracts\Taxes\TaxZone make()
 * @method static void save(\DuncanMcClean\Cargo\Contracts\Taxes\TaxZone $taxZone)
 * @method static void delete(string $handle)
 * @method static \Statamic\Fields\Blueprint blueprint()
 *
 * @see \DuncanMcClean\Cargo\Taxes\TaxZoneRepository
 */
class TaxZone extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
