<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Discounts;

use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Http\Resources\CP\Discounts\Discount as DiscountResource;
use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;

class DiscountActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Discount::find($item);
        });
    }

    protected function getItemData($discount, $context): array
    {
        $discount = $discount->fresh();

        return array_merge((new DiscountResource($discount))->resolve()['data'], [
            'itemActions' => Action::for($discount, $context),
        ]);
    }
}
