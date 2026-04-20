<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class AugmentedOrderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        AssetContainer::make()->handle('assets')->disk('local')->save();
    }

    protected function tearDown(): void
    {
        Collection::find('products')?->entryBlueprint()?->delete();

        parent::tearDown();
    }

    #[Test]
    public function downloads_uses_order_id_for_download_urls()
    {
        $collection = tap(Collection::make('products'))->save();
        $collection->entryBlueprint()->ensureField('downloads', ['type' => 'assets']);

        $product = Entry::make()
            ->id('digital-product')
            ->slug('digital-product')
            ->collection('products')
            ->data([
                'type' => 'digital',
                'downloads' => ['one.png'],
            ]);

        $product->save();

        $order = Order::make()
            ->id('test-order-id')
            ->lineItems([
                ['id' => 'line-item-id', 'product' => 'digital-product', 'download_count' => 0],
            ]);

        $order->save();

        $augmented = $order->augmentedValue('downloads');
        $downloads = $augmented->value();

        $this->assertCount(1, $downloads);
        $this->assertStringContainsString('test-order-id', $downloads->first()['download_url']);
    }
}
