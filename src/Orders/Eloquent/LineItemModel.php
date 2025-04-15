<?php

namespace DuncanMcClean\Cargo\Orders\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineItemModel extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'sub_total' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'data' => 'json',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statamic.cargo.orders.line_items_table', 'cargo_order_line_items'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(app('cargo.orders.eloquent.model'), ownerKey: 'order_id');
    }
}
