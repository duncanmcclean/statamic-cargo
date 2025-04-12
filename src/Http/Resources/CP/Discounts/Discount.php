<?php

namespace DuncanMcClean\Cargo\Http\Resources\CP\Discounts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Discount extends JsonResource
{
    public function toArray(Request $request)
    {
        $data = [
            'id' => $this->resource->id(),
            'reference' => $this->resource->reference(),
            'title' => $this->resource->name(),
            'type' => $this->resource->type(),
            'amount' => $this->resource->amount(),
            'edit_url' => $this->resource->editUrl(),
            'redeemed_count' => $this->resource->redeemedCount(),
        ];

        return ['data' => $data];
    }
}
