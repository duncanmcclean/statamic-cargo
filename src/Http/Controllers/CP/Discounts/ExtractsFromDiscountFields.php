<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Discounts;

trait ExtractsFromDiscountFields
{
    protected function extractFromFields($discount, $blueprint)
    {
        $values = $discount->data()
            ->merge([
                'name' => $discount->name(),
                'code' => $discount->code(),
                'type' => $discount->type(),
                'amount' => $discount->amount(),
            ]);

        $fields = $blueprint
            ->fields()
            ->setParent($discount)
            ->addValues($values->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
