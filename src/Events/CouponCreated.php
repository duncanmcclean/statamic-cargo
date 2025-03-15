<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Coupons\Coupon;
use Statamic\Events\Event;

class CouponCreated extends Event
{
    public function __construct(public Coupon $coupon) {}
}
