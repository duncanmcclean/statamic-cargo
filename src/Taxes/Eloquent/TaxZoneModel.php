<?php

namespace DuncanMcClean\Cargo\Taxes\Eloquent;

use Illuminate\Database\Eloquent\Model;

class TaxZoneModel extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $primaryKey = 'handle';

    protected $keyType = 'string';

    public function getTable(): string
    {
        return config('statamic.cargo.taxes.tax_zones.table', 'cargo_tax_zones');
    }

    public function casts(): array
    {
        return [
            'data' => 'json',
        ];
    }
}
