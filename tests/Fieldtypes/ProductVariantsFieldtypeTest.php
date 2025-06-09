<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\Cargo\Fieldtypes\ProductVariants;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Tests\TestCase;

class ProductVariantsFieldtypeTest extends TestCase
{
    #[Test]
    public function can_preload()
    {
        $preload = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->preload();

        $this->assertIsArray($preload);

        $this->assertCount(2, $preload['variants']['fields']);
        $this->assertCount(2, $preload['variants']['new']);
        $this->assertCount(0, $preload['variants']['existing']);

        $this->assertCount(3, $preload['options']['fields']);
        $this->assertEquals(array_column($preload['options']['fields'], 'handle'), ['key', 'variant', 'price']);
        $this->assertCount(3, $preload['options']['defaults']);
        $this->assertCount(3, $preload['options']['new']);
        $this->assertCount(0, $preload['options']['existing']);
    }

    #[Test]
    public function can_pre_process()
    {
        $preProcess = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->preProcess([
                'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                    ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                    ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
                ],
            ]);

        $this->assertIsArray($preProcess);

        $this->assertIsArray($preProcess['variants']);
        $this->assertCount(1, $preProcess['variants']);

        // Ensures the 'Price' field has been processed.
        $this->assertEquals($preProcess['options'][0]['price'], '10.00');
        $this->assertEquals($preProcess['options'][1]['price'], '15.00');
        $this->assertEquals($preProcess['options'][2]['price'], '17.99');
    }

    #[Test]
    public function can_process()
    {
        $process = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->process([
                'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => '10.00'],
                    ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => '15.00'],
                    ['key' => 'Blue', 'variant' => 'Blue', 'price' => '17.99'],
                ],
            ]);

        $this->assertIsArray($process);

        $this->assertIsArray($process['variants']);
        $this->assertCount(1, $process['variants']);

        // Ensures the 'Price' field has been processed.
        $this->assertEquals($process['options'][0]['price'], 1000);
        $this->assertEquals($process['options'][1]['price'], 1500);
        $this->assertEquals($process['options'][2]['price'], 1799);
    }

    #[Test]
    public function can_process_with_no_variants_and_no_options()
    {
        $process = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->process([
                'variants' => [],
                'options' => [],
            ]);

        $this->assertNull($process);
    }

    #[Test]
    public function can_augment()
    {
        $augment = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->augment([
                'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                    ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                    ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
                ],
            ]);

        $this->assertIsArray($augment);

        $this->assertEquals([
            'name' => 'Colour',
            'values' => ['Red', 'Yellow', 'Blue'],
        ], collect($augment['variants'][0])->map->value()->all());

        $this->assertEquals([
            'key' => 'Red',
            'variant' => 'Red',
            'price' => '£10.00',
        ], collect($augment['options'][0])->map->value()->all());

        $this->assertEquals([
            'key' => 'Yellow',
            'variant' => 'Yellow',
            'price' => '£15.00',
        ], collect($augment['options'][1])->map->value()->all());

        $this->assertEquals([
            'key' => 'Blue',
            'variant' => 'Blue',
            'price' => '£17.99',
        ], collect($augment['options'][2])->map->value()->all());
    }

    #[Test]
    public function can_pre_process_index_with_no_variants()
    {
        $preProcessIndex = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->preProcessIndex([
                'variants' => [],
                'options' => [],
            ]);

        $this->assertIsString($preProcessIndex);
        $this->assertEquals('No variants.', $preProcessIndex);
    }

    #[Test]
    public function can_pre_process_index_with_one_variant()
    {
        $preProcessIndex = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->preProcessIndex([
                'variants' => ['name' => 'Colour', 'values' => ['Red']],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                ],
            ]);

        $this->assertIsString($preProcessIndex);
        $this->assertEquals('1 variant', $preProcessIndex);
    }

    #[Test]
    public function can_pre_process_index_with_multiple_variants()
    {
        $preProcessIndex = (new ProductVariants)
            ->setField(new Field('product_variants', []))
            ->preProcessIndex([
                'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                    ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                    ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
                ],
            ]);

        $this->assertIsString($preProcessIndex);
        $this->assertEquals('3 variants', $preProcessIndex);
    }

    #[Test]
    public function returns_extra_validation_rules()
    {
        $extraRules = (new ProductVariants)
            ->setField(new Field('product_variants', [
                'option_fields' => [
                    [
                        'handle' => 'size',
                        'field' => [
                            'type' => 'text',
                            'validate' => 'required|min:10|max:20',
                        ],
                    ],
                ],
            ]))
            ->extraRules();

        $this->assertIsArray($extraRules);

        $this->assertEquals([
            'product_variants.variants' => ['array'],
            'product_variants.options' => ['array'],
            'product_variants.variants.*.name' => ['required'],
            'product_variants.variants.*.values' => ['required'],
            'product_variants.options.*.key' => ['required'],
            'product_variants.options.*.variant' => ['required'],
            'product_variants.options.*.price' => ['required'],
            'product_variants.options.*.size' => ['required', 'min:10', 'max:20'],
        ], $extraRules);
    }
}
