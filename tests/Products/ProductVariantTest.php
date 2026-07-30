<?php

namespace Tests\Products;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    protected function tearDown(): void
    {
        Collection::find('products')?->entryBlueprint()?->delete();

        parent::tearDown();
    }

    #[Test]
    public function blueprint_does_not_throw_when_a_default_option_field_has_been_overridden()
    {
        Collection::find('products')->entryBlueprint()->ensureField('product_variants', [
            'type' => 'product_variants',
            'option_fields' => [
                [
                    'handle' => 'price',
                    'field' => [
                        'type' => 'money',
                        'display' => 'Price',
                        'validate' => ['required', 'numeric'],
                    ],
                ],
            ],
        ])->save();

        $product = tap(Entry::make()->collection('products')->data(['product_variants' => [
            'variants' => [['name' => 'Colour', 'values' => ['Red']]],
            'options' => [['key' => 'Red', 'variant' => 'Red', 'price' => 1000]],
        ]]))->save();

        $blueprint = $product->variant('Red')->blueprint();

        $this->assertEquals(['key', 'variant', 'price'], $blueprint->fields()->all()->keys()->all());
        $this->assertEquals(['required', 'numeric'], $blueprint->field('price')->get('validate'));
    }
}
