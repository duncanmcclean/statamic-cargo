<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Facades\ShippingMethod;
use Statamic\Fieldtypes\Relationship;

class ShippingMethods extends Relationship
{
    protected $selectable = false;
    protected $indexComponent = null;

    protected function toItemArray($id)
    {
        $shippingMethod = ShippingMethod::find($id);

        return [
            'name' => $shippingMethod->title(),
            'handle' => $shippingMethod->handle(),
        ];
    }

    public function getIndexItems($request)
    {
        //
    }

    public function augment($values)
    {
        if ($this->config('max_items') === 1) {
            $shippingMethod = ShippingMethod::find($values);

            return [
                'name' => $shippingMethod->title(),
                'handle' => $shippingMethod->handle(),
            ];
        }

        return collect($values)->map(function (string $handle) {
            $shippingMethod = ShippingMethod::find($handle);

            return [
                'name' => $shippingMethod->title(),
                'handle' => $shippingMethod->handle(),
            ];
        })->filter()->all();
    }

    public function preProcessIndex($data)
    {
        return collect($data)->map(function ($item) {
            $shippingMethod = ShippingMethod::find($item);

            return $shippingMethod?->title();
        })->implode(', ');
    }

    public function rules(): array
    {
        if ($this->config('max_items') === 1) {
            return ['string'];
        }

        return parent::rules();
    }
}
