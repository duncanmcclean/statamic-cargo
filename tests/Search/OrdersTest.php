<?php

namespace Search;

use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Search\OrdersProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_discounts()
    {
        Order::make()->id('a')->save();
        Order::make()->id('b')->save();

        $provider = $this->makeProvider('en', ['searchables' => 'orders']);

        // Check if it provides the expected orders.
        $this->assertEquals([
            'order::a', 'order::b',
        ], $provider->provide()->all());

        // Check if the orders are contained by the provider or not.
        foreach (Discount::all() as $order) {
            $this->assertEquals(
                true,
                $provider->contains($order),
                "Order {$order->id()} should be contained in the provider."
            );
        }
    }

    private function makeProvider($locale, $config)
    {
        $index = $this->makeIndex($locale, $config);

        $keys = $this->normalizeSearchableKeys($config['searchables'] ?? null);

        return (new OrdersProvider)->setIndex($index)->setKeys($keys);
    }

    private function makeIndex($locale, $config)
    {
        $index = $this->mock(\Statamic\Search\Index::class);

        $index->shouldReceive('config')->andReturn($config);
        $index->shouldReceive('locale')->andReturn($locale);

        return $index;
    }

    private function normalizeSearchableKeys($keys)
    {
        // a bit of duplicated implementation logic.
        // but it makes the test look more like the real thing.
        return collect($keys === 'all' ? ['*'] : $keys)
            ->map(fn ($key) => str_replace('orders:', '', $key))
            ->all();
    }
}
