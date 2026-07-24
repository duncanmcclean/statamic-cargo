<?php

namespace Tests\Query\Scopes\Filters;

use DuncanMcClean\Cargo\Events\OrderBlueprintFound;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Query\Scopes\Filters\Fields;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Scope;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function is_visible_to_the_orders_listing()
    {
        $this->assertTrue((new Fields)->visibleTo('orders'));
        $this->assertFalse((new Fields)->visibleTo('entries'));
    }

    #[Test]
    public function is_registered_as_a_filter()
    {
        $this->assertTrue(
            Scope::filters('orders')->contains(fn ($filter) => $filter instanceof Fields)
        );
    }

    #[Test]
    public function only_lists_filterable_fields()
    {
        Event::listen(OrderBlueprintFound::class, function ($event) {
            $event->blueprint->ensureField('color', ['type' => 'text']);
            $event->blueprint->ensureField('notes', ['type' => 'text', 'filterable' => false]);
        });

        $handles = collect((new Fields)->extra())->pluck('handle');

        $this->assertContains('color', $handles);
        $this->assertNotContains('notes', $handles);
        $this->assertNotContains('line_items', $handles);
        $this->assertNotContains('status', $handles);
        $this->assertNotContains('customer', $handles);
    }

    #[Test]
    public function can_filter_orders_by_a_field()
    {
        Event::listen(OrderBlueprintFound::class, function ($event) {
            $event->blueprint->ensureField('color', ['type' => 'text']);
        });

        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->merge(['color' => 'red'])->save();
        Order::make()->id('456')->cart('def')->merge(['color' => 'blue'])->save();
        Order::make()->id('789')->cart('ghi')->merge(['color' => 'red'])->save();

        $query = Order::query();

        (new Fields)->apply($query, ['color' => ['operator' => '=', 'value' => 'red']]);

        $results = $query->get();

        $this->assertCount(2, $results);
        $this->assertEquals([123, 789], $results->map->id()->all());
    }
}
