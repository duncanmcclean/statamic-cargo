<?php

namespace DuncanMcClean\Cargo\Http\Resources\CP\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
{
    public function toArray(Request $request)
    {
        $data = [
            'title' => __('Order #:number', ['number' => $this->orderNumber()]),
            'order_number' => $this->orderNumber(),
        ];

        return ['data' => $data];
    }
}
