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
            'title' => $this->resource->title(),
            'type' => $this->resource->type(),
            'edit_url' => $this->resource->editUrl(),
        ];

        return ['data' => $data];
    }
}
