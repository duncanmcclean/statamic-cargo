<?php

namespace DuncanMcClean\Cargo\Http\Resources\CP\Discounts;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
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

        return [
            'id' => $discount->handle(),
            'name' => $discount->name(),
            'status' => $this->status(),

            $this->merge($this->values(['type' => $discount->type()])),

            'edit_url' => $discount->editUrl(),
            'viewable' => User::current()->can('view', $discount),
            'editable' => User::current()->can('edit', $discount),
            'actions' => Action::for($discount),
        ];
    }

    protected function status()
    {
        if ($this->resource->get('start_date') !== null) {
            if (Carbon::parse($this->resource->get('start_date'))->isFuture()) {
                return 'scheduled';
            }
        }

        if ($this->resource->has('end_date') && $this->resource->get('end_date') !== null) {
            if (Carbon::parse($this->resource->get('end_date'))->isPast()) {
                return 'expired';
            }
        }

        return 'active';
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
