<?php

namespace Tests\Search;

use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Search\DiscountsProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class DiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_discounts()
    {
        Discount::make()->handle('a')->save();
        Discount::make()->handle('b')->save();

        $provider = $this->makeProvider('en', ['searchables' => 'discounts']);

        // Check if it provides the expected discounts.
        $this->assertEquals([
            'discount::a', 'discount::b',
        ], $provider->provide()->all());

        // Check if the discounts are contained by the provider or not.
        foreach (Discount::all() as $discount) {
            $this->assertEquals(
                true,
                $provider->contains($discount),
                "Discount {$discount->handle()} should be contained in the provider."
            );
        }
    }

    private function makeProvider($locale, $config)
    {
        $index = $this->makeIndex($locale, $config);

        $keys = $this->normalizeSearchableKeys($config['searchables'] ?? null);

        return (new DiscountsProvider)->setIndex($index)->setKeys($keys);
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
            ->map(fn ($key) => str_replace('discounts:', '', $key))
            ->all();
    }
}
