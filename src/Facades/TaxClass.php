<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClassRepository as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Taxes\TaxClass find(string $handle)
 * @method static \DuncanMcClean\Cargo\Contracts\Taxes\TaxClass make()
 * @method static void save(\DuncanMcClean\Cargo\Contracts\Taxes\TaxClass $taxClass)
 * @method static void delete(string $handle)
 * @method static \Statamic\Fields\Blueprint blueprint()
 *
 * @see \DuncanMcClean\Cargo\Taxes\TaxClassRepository
 */
class TaxClass extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
