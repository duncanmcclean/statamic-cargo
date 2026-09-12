<?php

namespace DuncanMcClean\Cargo\Orders;

use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Fields\Field;

class LineItemBlueprint
{
    public function __invoke(): StatamicBlueprint
    {
        $blueprint = BlueprintFacade::makeFromFields([
            'product' => ['type' => 'entries', 'max_items' => 1, 'collections' => config('statamic.cargo.products.collections')],
            'variant' => ['type' => 'text'],
            'quantity' => ['type' => 'integer'],
            'unit_price' => ['type' => 'money', 'save_zero_value' => true],
            'sub_total' => ['type' => 'money', 'save_zero_value' => true],
            'tax_total' => ['type' => 'money', 'save_zero_value' => true],
            'discount_total' => ['type' => 'money', 'save_zero_value' => true],
            'total' => ['type' => 'money', 'save_zero_value' => true],
        ])->setHandle('line_item');

        BlueprintFacade::find('cargo::line_item')?->fields()->all()
            ->reject(fn (Field $field) => $blueprint->hasField($field->handle()))
            ->each(fn (Field $field) => $blueprint->ensureField($field->handle(), $field->config()));

        return $blueprint;
    }
}
