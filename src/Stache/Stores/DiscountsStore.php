<?php

namespace DuncanMcClean\Cargo\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Entries\GetSlugFromPath;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class DiscountsStore extends BasicStore
{
    protected $storeIndexes = [
        'name', 'code',
    ];

    public function key()
    {
        return 'discounts';
    }

    public function makeItemFromFile($path, $contents): DiscountContract
    {
        $data = YAML::file($path)->parse($contents);

        return Discount::make()
            ->id(Arr::pull($data, 'id'))
            ->name(Arr::pull($data, 'name'))
            ->code(Arr::pull($data, 'code'))
            ->type(Arr::pull($data, 'type'))
            ->amount(Arr::pull($data, 'amount'))
            ->data($data);
    }
}
