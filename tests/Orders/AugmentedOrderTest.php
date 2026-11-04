<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $order = $this->makeOrderWithDigitalProduct(OrderStatus::PaymentReceived);

        $downloads = $order->augmentedValue('downloads')->value();

        $this->assertCount(1, $downloads);
        $this->assertStringContainsString('test-order-id', $downloads->first()['download_url']);
    }

    #[Test]
    #[DataProvider('unpaidOrderStatusProvider')]
    public function downloads_are_empty_when_order_has_not_been_paid(OrderStatus $status)
    {
        $order = $this->makeOrderWithDigitalProduct($status);

        $downloads = $order->augmentedValue('downloads')->value();

        $this->assertCount(0, $downloads);
    }

    public static function unpaidOrderStatusProvider(): array
    {
        return [
            'payment pending' => [OrderStatus::PaymentPending],
            'cancelled' => [OrderStatus::Cancelled],
            'returned' => [OrderStatus::Returned],
        ];
    }

    private function makeOrderWithDigitalProduct(OrderStatus $status)
    {
        $collection = tap(Collection::make('products'))->save();
        $collection->entryBlueprint()->ensureField('downloads', ['type' => 'assets']);

        Entry::make()
            ->id('digital-product')
            ->slug('digital-product')
            ->collection('products')
            ->data([
                'type' => 'digital',
                'downloads' => ['one.png'],
            ])
            ->save();

        $order = Order::make()
            ->id('test-order-id')
            ->status($status)
            ->lineItems([
                ['id' => 'line-item-id', 'product' => 'digital-product', 'download_count' => 0],
            ]);

        $order->save();

        return $order;
    }
}
