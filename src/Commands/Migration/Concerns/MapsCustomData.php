<?php

namespace DuncanMcClean\Cargo\Commands\Migration\Concerns;

use Illuminate\Support\Collection;

trait MapsCustomData
{
    private function mapCustomData(Collection $data): array
    {
        return $data->except([
            'id',
            'blueprint',
            'order_number',
            'title',
            'slug',
            'order_date',
            'updated_by',
            'updated_at',
            'shipping_name',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_address',
            'shipping_address_line1',
            'shipping_address_line2',
            'shipping_city',
            'shipping_country',
            'shipping_region',
            'shipping_zip_code',
            'shipping_postal_code',
            'billing_name',
            'billing_first_name',
            'billing_last_name',
            'billing_address',
            'billing_address_line1',
            'billing_address_line2',
            'billing_city',
            'billing_country',
            'billing_region',
            'billing_zip_code',
            'billing_postal_code',
            'use_shipping_address_for_billing',
            'shipping_tax',
            'status_log',
            'order_status',
            'payment_status',
            'items',
            'grand_total',
            'items_total',
            'tax_total',
            'shipping_total',
            'coupon_total',
            'customer',
            'gateway',
            'coupon',
            'shipping_method',
            'data_shipping_method',
            'data->shipping_method',
            'created_at',
            'updated_at',
            'customer_id',
            'site',
            'seo',
        ])->all();
    }
}
