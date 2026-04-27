<?php

namespace DuncanMcClean\Cargo\Taxes\Eloquent;

use Illuminate\Database\Eloquent\Model;

class TaxClassModel extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $primaryKey = 'handle';

    protected $keyType = 'string';

    public function getTable(): string
    {
        return config('statamic.cargo.taxes.tax_classes.table', 'cargo_tax_classes');
    }

    public function casts(): array
    {
        return [
            'data' => 'json',
        ];
    }
}
