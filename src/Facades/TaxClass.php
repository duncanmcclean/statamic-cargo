<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClassRepository as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Taxes\TaxClassRepository
 */
class TaxClass extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
