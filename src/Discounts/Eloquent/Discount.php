<?php

namespace DuncanMcClean\Cargo\Discounts\Eloquent;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Discounts\Discount as StacheDiscount;

class Discount extends StacheDiscount
{
    protected $model;

    public static function fromModel(DiscountModel $model): self
    {
        return (new static)
            ->model($model)
            ->handle($model->handle)
            ->title($model->title)
            ->type($model->type)
            ->data([
                ...$model->data ?? [],
                'updated_at' => $model->updated_at,
            ])
            ->syncOriginal();
    }

    public static function makeModelFromContract(DiscountContract $source): DiscountModel
    {
        $class = app('cargo.discounts.eloquent.model');

        $attributes = [
            'handle' => $source->handle(),
            'title' => $source->title(),
            'type' => $source->type(),
            'data' => $source->data()->except('updated_at')->all(),
        ];

        return $class::firstOrNew(['handle' => $source->handle()])->fill($attributes);
    }

    public function toModel()
    {
        return self::makeModelFromContract($this);
    }

    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        return $this;
    }

    public function defaultAugmentedArrayKeys()
    {
        return [];
    }
}
