<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Discounts\QueryBuilder query()
 * @method static \DuncanMcClean\Cargo\Contracts\Discounts\Discount find($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Discounts\Discount findOrFail($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Discounts\Discount findByCode(string $code)
 * @method static \DuncanMcClean\Cargo\Contracts\Discounts\Discount make()
 * @method static void save(\DuncanMcClean\Cargo\Contracts\Discounts\Discount $coupon)
 * @method static void delete(\DuncanMcClean\Cargo\Contracts\Discounts\Discount $coupon)
 * @method static \Statamic\Fields\Blueprint blueprint()
 *
 * @see \DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository
 */
class Discount extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DiscountRepository::class;
    }
}
