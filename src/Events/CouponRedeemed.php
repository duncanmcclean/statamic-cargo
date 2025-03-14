<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Coupons\Coupon;
use DuncanMcClean\Cargo\Contracts\Orders\Order;

class CouponRedeemed
{
    public function __construct(public Coupon $coupon, public Order $order) {}
}
