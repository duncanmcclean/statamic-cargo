<?php

namespace DuncanMcClean\Cargo\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Coupons\Coupon as CouponContract;
use DuncanMcClean\Cargo\Facades\Coupon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Entries\GetSlugFromPath;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class CouponsStore extends BasicStore
{
    protected $storeIndexes = [
        'code',
    ];

    public function key()
    {
        return 'coupons';
    }

    public function makeItemFromFile($path, $contents): CouponContract
    {
        $data = YAML::file($path)->parse($contents);

        return Coupon::make()
            ->id(Arr::pull($data, 'id'))
            ->code(Str::upper((new GetSlugFromPath)($path)))
            ->type(Arr::pull($data, 'type'))
            ->amount(Arr::pull($data, 'amount'))
            ->data($data);
    }
}
