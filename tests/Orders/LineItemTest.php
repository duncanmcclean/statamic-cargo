<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Events\LineItemBlueprintFound;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Orders\LineItems;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class LineItemTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['title' => 'T-shirt', 'price' => 1500])->save();

        Entry::make()->id('variant-product-id')->collection('products')->data([
            'title' => 'Hoodie',
            'product_variants' => [
                'variants' => [['name' => 'Size', 'values' => ['Small']]],
                'options' => [['key' => 'Small', 'variant' => 'Small', 'price' => 3000]],
            ],
        ])->save();
    }

    #[Test]
    public function product_title_returns_the_title_of_the_product()
    {
        $lineItem = $this->makeLineItem(['product' => 'product-id', 'product_title' => 'Old T-shirt']);

        $this->assertEquals('T-shirt', $lineItem->productTitle());
    }

    #[Test]
    public function product_title_falls_back_to_the_stored_title_when_the_product_has_been_deleted()
    {
        $lineItem = $this->makeLineItem(['product' => 'product-id', 'product_title' => 'Old T-shirt']);

        Entry::find('product-id')->delete();

        $this->assertEquals('Old T-shirt', $lineItem->productTitle());
    }

    #[Test]
    public function product_title_is_null_when_the_product_has_been_deleted_and_no_title_was_stored()
    {
        $lineItem = $this->makeLineItem(['product' => 'product-id']);

        Entry::find('product-id')->delete();

        $this->assertNull($lineItem->productTitle());
    }

    #[Test]
    public function variant_name_returns_the_name_of_the_variant()
    {
        $lineItem = $this->makeLineItem(['product' => 'variant-product-id', 'variant' => 'Small', 'variant_name' => 'Old Small']);

        $this->assertEquals('Small', $lineItem->variantName());
    }

    #[Test]
    public function variant_name_falls_back_to_the_stored_name_when_the_product_has_been_deleted()
    {
        $lineItem = $this->makeLineItem(['product' => 'variant-product-id', 'variant' => 'Small', 'variant_name' => 'Old Small']);

        Entry::find('variant-product-id')->delete();

        $this->assertEquals('Old Small', $lineItem->variantName());
    }

    #[Test]
    public function variant_name_falls_back_to_the_stored_name_when_the_variant_has_been_deleted()
    {
        $lineItem = $this->makeLineItem(['product' => 'variant-product-id', 'variant' => 'Medium', 'variant_name' => 'Medium']);

        $this->assertEquals('Medium', $lineItem->variantName());
    }

    #[Test]
    public function variant_name_is_null_when_the_variant_has_been_deleted_and_no_name_was_stored()
    {
        $lineItem = $this->makeLineItem(['product' => 'variant-product-id', 'variant' => 'Medium']);

        $this->assertNull($lineItem->variantName());
    }

    #[Test]
    public function product_title_and_variant_name_can_be_augmented()
    {
        $lineItem = $this->makeLineItem(['product' => 'variant-product-id', 'variant' => 'Small', 'product_title' => 'Old Hoodie', 'variant_name' => 'Old Small']);

        $this->assertEquals('Hoodie', $lineItem->augmentedValue('product_title')->value());
        $this->assertEquals('Small', $lineItem->augmentedValue('variant_name')->value());

        Entry::find('variant-product-id')->delete();

        $this->assertEquals('Old Hoodie', $lineItem->augmentedValue('product_title')->value());
        $this->assertEquals('Old Small', $lineItem->augmentedValue('variant_name')->value());
    }

    #[Test]
    public function blueprint_includes_fields_from_custom_blueprint()
    {
        $customBlueprint = Blueprint::make('line_item')->setNamespace('cargo')->setContents([
            'tabs' => ['main' => ['sections' => [['fields' => [
                ['handle' => 'engraving', 'field' => ['type' => 'text', 'display' => 'Engraving Text']],
                ['handle' => 'quantity', 'field' => ['type' => 'text']],
            ]]]]],
        ]);

        Blueprint::partialMock()->shouldReceive('find')->with('cargo::line_item')->andReturn($customBlueprint);

        $blueprint = LineItems::blueprint();

        $this->assertEquals('Engraving Text', $blueprint->field('engraving')->display());
        $this->assertEquals('integer', $blueprint->field('quantity')->type());
    }

    #[Test]
    public function blueprint_can_be_extended_with_an_event()
    {
        Event::listen(LineItemBlueprintFound::class, function (LineItemBlueprintFound $event) {
            $event->blueprint->ensureField('engraving', ['type' => 'text']);
        });

        $this->assertTrue(LineItems::blueprint()->hasField('engraving'));
    }

    #[Test]
    public function metadata_only_includes_blueprint_fields_with_a_value()
    {
        Event::listen(LineItemBlueprintFound::class, function (LineItemBlueprintFound $event) {
            $event->blueprint->ensureField('engraving', ['type' => 'text', 'display' => 'Engraving Text']);
            $event->blueprint->ensureField('gift_wrap', ['type' => 'toggle']);
        });

        $lineItem = Order::make()->lineItems([
            ['product' => 'product-id', 'quantity' => 1, 'unit_price' => 1500, 'engraving' => 'Happy Birthday', 'gift_message' => 'Enjoy!', 'tax_breakdown' => []],
        ])->lineItems()->first();

        $metadata = $lineItem->metadata();

        $this->assertEquals(['engraving'], $metadata->keys()->all());
        $this->assertEquals('Engraving Text', $metadata->get('engraving')->display());
        $this->assertEquals('Happy Birthday', $metadata->get('engraving')->value());
    }

    #[Test]
    public function metadata_is_augmented()
    {
        Event::listen(LineItemBlueprintFound::class, function (LineItemBlueprintFound $event) {
            $event->blueprint->ensureField('engraving', ['type' => 'text', 'display' => 'Engraving Text']);
            $event->blueprint->ensureField('gift_wrap', ['type' => 'toggle', 'display' => 'Gift Wrap']);
        });

        $lineItem = Order::make()->lineItems([
            ['product' => 'product-id', 'quantity' => 1, 'unit_price' => 1500, 'engraving' => 'Happy Birthday', 'gift_wrap' => true],
        ])->lineItems()->first();

        $metadata = $lineItem->augmentedValue('metadata')->value();

        $this->assertCount(2, $metadata);
        $this->assertEquals('Engraving Text', $metadata[0]['display']);
        $this->assertEquals('Happy Birthday', $metadata[0]['value']->value());
        $this->assertEquals('Gift Wrap', $metadata[1]['display']);
        $this->assertTrue($metadata[1]['value']->value());
    }

    private function makeLineItem(array $lineItem): LineItem
    {
        return Order::make()->lineItems([$lineItem])->lineItems()->first();
    }
}
