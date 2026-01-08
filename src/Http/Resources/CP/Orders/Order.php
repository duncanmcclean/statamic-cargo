<?php

namespace DuncanMcClean\Cargo\Http\Resources\CP\Orders;

use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\User;

class Order extends JsonResource
{
    public function toArray(Request $request)
    {
        $data = [
            'id' => $this->id(),
            'reference' => $this->reference(),
            'order_number' => $this->orderNumber(),
            'date' => $this->date(),
            'status' => $this->status(),
            'grand_total' => Money::format($this->grandTotal(), $this->site()),
            'edit_url' => cp_route('cargo.orders.edit', $this->id()),
            'title' => __('Order #:number', ['number' => $this->orderNumber()]),
            'editable' => User::current()->can('edit', $this->resource),
        ];

        return ['data' => $data];
    }
}
