<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZoneRepository as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Taxes\TaxZoneRepository
 */
class TaxZone extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
