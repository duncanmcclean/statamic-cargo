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
        'code',
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
            ->code(Str::upper((new GetSlugFromPath)($path)))
            ->type(Arr::pull($data, 'type'))
            ->amount(Arr::pull($data, 'amount'))
            ->data($data);
    }
}
