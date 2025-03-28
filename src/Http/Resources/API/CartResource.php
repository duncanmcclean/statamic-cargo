<?php

namespace DuncanMcClean\Cargo\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $response = $this->resource
            ->toAugmentedCollection()
            ->withShallowNesting()
            ->toArray();

        $response['line_items'] = array_map(function ($item) {
            $item['product'] = $item['product']->value()->toAugmentedArray();

            unset($item['product']['updated_by']);

            return $item;
        }, $response['line_items']->value());

        return $response;
    }
}
