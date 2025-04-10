<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\Discount;
use Statamic\Fieldtypes\Relationship;

class Discounts extends Relationship
{
    protected $selectable = false;

    protected function toItemArray($id)
    {
        //
    }

    public function getIndexItems($request)
    {
        //
    }

    public function augment($values)
    {
        if ($this->config('max_items') == 1) {
            return Discount::find($values)?->toShallowAugmentedArray();
        }

        return collect($values)->map(fn ($id) => Discount::find($id)?->toShallowAugmentedArray())->filter()->all();
    }

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $coupon = Discount::find($item);

            return [
                'id' => $coupon->id(),
                'title' => $coupon->code(),
                'edit_url' => $coupon->editUrl(),
            ];
        });
    }
}
