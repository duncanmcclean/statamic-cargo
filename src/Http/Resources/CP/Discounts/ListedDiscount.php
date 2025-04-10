<?php

namespace DuncanMcClean\Cargo\Http\Resources\CP\Discounts;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedDiscount extends JsonResource
{
    protected $blueprint;

    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        $discount = $this->resource;

        ray($discount);

        return [
            'id' => $discount->id(),
            'code' => $discount->code(),
            'type' => $discount->type()->value,
            'amount' => $discount->amount(),
            'discount_text' => $discount->discountText(),

            $this->merge($this->values()),

            'edit_url' => $discount->editUrl(),
            'viewable' => User::current()->can('view', $discount),
            'editable' => User::current()->can('edit', $discount),
            'actions' => Action::for($discount),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            $value = $extra[$key] ?? $this->resource->get($key);

            if (! $field) {
                return [$key => $value];
            }

            $value = $field->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
