<?php

namespace DuncanMcClean\Cargo\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Support\Arr;
use Statamic\Entries\GetSlugFromPath;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class DiscountsStore extends BasicStore
{
    protected $storeIndexes = [
        'handle', 'type', 'discount_code',
    ];

    public function key()
    {
        return 'discounts';
    }

    public function makeItemFromFile($path, $contents): DiscountContract
    {
        $data = YAML::file($path)->parse($contents);

        return Discount::make()
            ->handle((new GetSlugFromPath)($path))
            ->title(Arr::pull($data, 'title'))
            ->type(Arr::pull($data, 'type'))
            ->data($data);
    }
}
