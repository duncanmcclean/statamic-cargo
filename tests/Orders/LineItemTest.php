<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Events\LineItemBlueprintFound;
use DuncanMcClean\Cargo\Facades\Order;
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
}
