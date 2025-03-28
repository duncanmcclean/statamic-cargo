<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Coupons\CouponRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \DuncanMcClean\Cargo\Contracts\Coupons\QueryBuilder query()
 * @method static \DuncanMcClean\Cargo\Contracts\Coupons\Coupon find($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Coupons\Coupon findOrFail($id)
 * @method static \DuncanMcClean\Cargo\Contracts\Coupons\Coupon findByCode(string $code)
 * @method static \DuncanMcClean\Cargo\Contracts\Coupons\Coupon make()
 * @method static void save(\DuncanMcClean\Cargo\Contracts\Coupons\Coupon $coupon)
 * @method static void delete(\DuncanMcClean\Cargo\Contracts\Coupons\Coupon $coupon)
 * @method static \Statamic\Fields\Blueprint blueprint()
 *
 * @see \DuncanMcClean\Cargo\Contracts\Coupons\CouponRepository
 */
class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
