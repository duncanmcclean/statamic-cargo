<?php

namespace DuncanMcClean\Cargo\Discounts;

use Illuminate\Contracts\Support\Arrayable;

class DiscountCalculation implements Arrayable
{
    public $discount;
    public $description;
    public $amount;

    public static function make($discount, $description, $amount): self
    {
        return (new self)
            ->discount($discount)
            ->description($description)
            ->amount($amount);
    }

    public function discount($discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function description($description): self
    {
        $this->description = $description;

        return $this;
    }

    public function amount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'discount' => $this->discount,
            'description' => $this->description,
            'amount' => $this->amount,
        ];
    }
}
