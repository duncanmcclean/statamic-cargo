<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Coupons\Coupon;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class CouponDeleted implements ProvidesCommitMessage
{
    public function __construct(public Coupon $coupon) {}

    public function commitMessage()
    {
        return __('Coupon deleted', [], config('statamic.git.locale'));
    }
}
