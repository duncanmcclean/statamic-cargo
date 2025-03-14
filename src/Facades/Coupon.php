<?php

namespace DuncanMcClean\Cargo\Facades;

use DuncanMcClean\Cargo\Contracts\Coupons\CouponRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DuncanMcClean\Cargo\Contracts\Coupons\CouponRepository
 */
class Coupon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CouponRepository::class;
    }
}
