<?php

namespace DuncanMcClean\Cargo\Discounts\Eloquent;

use Illuminate\Database\Eloquent\Model;

class DiscountModel extends Model
{
    protected $guarded = [];

    public function casts(): array
    {
        return [
            'data' => 'json',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statamic.cargo.discounts.table', 'cargo_discounts'));
    }
}
