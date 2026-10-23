<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Fieldtypes\OrderReceipt;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Field;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrderReceiptFieldtypeTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();

        Entry::make()->id('product-id')->collection('products')->data([
            'title' => 'Hoodie',
            'product_variants' => [
                'variants' => [['name' => 'Size', 'values' => ['Small']]],
                'options' => [['key' => 'Small', 'variant' => 'Small', 'price' => 3000]],
            ],
        ])->save();
    }

    #[Test]
    public function it_returns_the_product_and_variant()
    {
        $lineItem = $this->preProcessLineItem(['product' => 'product-id', 'variant' => 'Small', 'product_title' => 'Old Hoodie', 'variant_name' => 'Old Small']);

        $this->assertEquals('product-id', $lineItem['product']['id']);
        $this->assertEquals('Hoodie', $lineItem['product']['title']);
        $this->assertNotNull($lineItem['product']['edit_url']);
        $this->assertArrayNotHasKey('invalid', $lineItem['product']);

        $this->assertEquals(['key' => 'Small', 'name' => 'Small'], $lineItem['variant']);
    }

    #[Test]
    public function it_returns_the_stored_product_title_and_variant_name_when_the_product_has_been_deleted()
    {
        Entry::find('product-id')->delete();

        $lineItem = $this->preProcessLineItem(['product' => 'product-id', 'variant' => 'Small', 'product_title' => 'Old Hoodie', 'variant_name' => 'Old Small']);

        $this->assertEquals(['id' => 'product-id', 'title' => 'Old Hoodie'], $lineItem['product']);
        $this->assertEquals(['key' => 'Small', 'name' => 'Old Small'], $lineItem['variant']);
    }

    #[Test]
    public function it_returns_an_invalid_product_when_the_product_has_been_deleted_and_no_title_was_stored()
    {
        Entry::find('product-id')->delete();

        $lineItem = $this->preProcessLineItem(['product' => 'product-id', 'variant' => 'Small']);

        $this->assertEquals(['id' => 'product-id', 'title' => 'product-id', 'invalid' => true], $lineItem['product']);
        $this->assertNull($lineItem['variant']);
    }

    private function preProcessLineItem(array $lineItem): array
    {
        $order = Order::make()->lineItems([['id' => 'line-item-id', 'quantity' => 1, ...$lineItem]]);

        $fieldtype = (new OrderReceipt)->setField((new Field('receipt', ['type' => 'order_receipt']))->setParent($order));

        return $fieldtype->preProcess(null)['line_items'][0];
    }
}
