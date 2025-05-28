<?php

namespace Tests\Products;

use DuncanMcClean\Cargo\Events\ProductDownloaded;
use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ProductDownloadTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        AssetContainer::make()->handle('assets')->disk('local')->save();

        File::ensureDirectoryExists(storage_path('app/private'));

        $this->withoutExceptionHandling();
    }

    #[Test]
    public function can_download_a_product()
    {
        Event::fake();

        File::put(storage_path('app/private/one.png'), '');
        Asset::make()->container('assets')->path('one.png')->save();

        $product = $this->makeProductWithDownloads(['one.png']);
        $order = $this->makeOrderWithLineItem($product);

        $this->assertEquals(0, $order->lineItems()->first()->download_count);

        $this
            ->get($this->generateDownloadUrl('order-id', 'line-item-id'))
            ->assertOk()
            ->assertDownload('one.png');

        $this->assertEquals(1, $order->fresh()->lineItems()->first()->download_count);

        Event::assertDispatched(
            ProductDownloaded::class,
            fn ($event) => $event->order->id() === 'order-id' && $event->lineItem->id() === 'line-item-id'
        );
    }

    #[Test]
    public function can_download_a_product_with_multiple_files()
    {
        Event::fake();

        File::put(storage_path('app/private/one.png'), '');
        Asset::make()->container('assets')->path('one.png')->save();

        File::put(storage_path('app/private/two.jpeg'), '');
        Asset::make()->container('assets')->path('two.jpeg')->save();

        File::put(storage_path('app/private/three.pdf'), '');
        Asset::make()->container('assets')->path('three.pdf')->save();

        $product = $this->makeProductWithDownloads(['one.png', 'two.jpeg', 'three.pdf']);
        $order = $this->makeOrderWithLineItem($product);

        $this->assertEquals(0, $order->lineItems()->first()->download_count);

        $this
            ->get($this->generateDownloadUrl('order-id', 'line-item-id'))
            ->assertOk()
            ->assertDownload('digital-product.zip');

        $this->assertEquals(1, $order->fresh()->lineItems()->first()->download_count);

        Event::assertDispatched(
            ProductDownloaded::class,
            fn ($event) => $event->order->id() === 'order-id' && $event->lineItem->id() === 'line-item-id'
        );
    }

    #[Test]
    public function can_download_a_variant_product()
    {
        Event::fake();

        File::put(storage_path('app/private/one.png'), '');
        Asset::make()->container('assets')->path('one.png')->save();

        $product = $this->makeVariantProductWithDownloads(['one.png']);
        $order = $this->makeOrderWithLineItem($product, ['variant' => 'Standard']);

        $this->assertEquals(0, $order->lineItems()->first()->download_count);

        $this
            ->get($this->generateDownloadUrl('order-id', 'line-item-id'))
            ->assertOk()
            ->assertDownload('one.png');

        $this->assertEquals(1, $order->fresh()->lineItems()->first()->download_count);

        Event::assertDispatched(
            ProductDownloaded::class,
            fn ($event) => $event->order->id() === 'order-id' && $event->lineItem->id() === 'line-item-id'
        );
    }

    #[Test]
    public function cant_download_a_product_with_no_downloads()
    {
        Event::fake();

        $product = $this->makeProductWithDownloads([]);
        $order = $this->makeOrderWithLineItem($product);

        $this->assertEquals(0, $order->lineItems()->first()->download_count);

        $this
            ->get($this->generateDownloadUrl('order-id', 'line-item-id'))
            ->assertNotFound();

        $this->assertEquals(0, $order->fresh()->lineItems()->first()->download_count);

        Event::assertNotDispatched(
            ProductDownloaded::class,
            fn ($event) => $event->order->id() === 'order-id' && $event->lineItem->id() === 'line-item-id'
        );
    }

    #[Test]
    public function cant_download_when_download_limit_has_been_reached()
    {
        Event::fake();

        File::put(storage_path('app/private/one.png'), '');
        Asset::make()->container('assets')->path('one.png')->save();

        $product = $this->makeProductWithDownloads(['one.png']);
        $product->set('download_limit', 5)->save();

        $order = $this->makeOrderWithLineItem($product, ['download_count' => 5]);

        $this->assertEquals(5, $order->lineItems()->first()->download_count);

        $this
            ->get($this->generateDownloadUrl('order-id', 'line-item-id'))
            ->assertForbidden();

        $this->assertEquals(5, $order->fresh()->lineItems()->first()->download_count);

        Event::assertNotDispatched(
            ProductDownloaded::class,
            fn ($event) => $event->order->id() === 'order-id' && $event->lineItem->id() === 'line-item-id'
        );
    }

    #[Test]
    public function throws_404_when_signature_is_invalid()
    {
        $this
            ->get('/!/cargo/download/order-id/line-item-id')
            ->assertForbidden();
    }

    #[Test]
    public function throws_404_when_order_cant_be_found()
    {
        $this
            ->get($this->generateDownloadUrl('order-id', 'line-item-id'))
            ->assertNotFound();
    }

    #[Test]
    public function throws_404_when_line_item_cant_be_found()
    {
        Order::make()->id('order-id')->save();

        $this
            ->get($this->generateDownloadUrl('order-id', 'unknown-line-item-id'))
            ->assertNotFound();
    }

    private function makeProductWithDownloads(array $downloads = [])
    {
        $collection = tap(Collection::make('products'))->save();
        $collection->entryBlueprint()->ensureField('downloads', ['type' => 'assets']);

        $product = Entry::make()
            ->id('digital-product')
            ->slug('digital-product')
            ->collection('products')
            ->data([
                'type' => 'digital',
                'downloads' => $downloads,
            ]);

        $product->save();

        return $product;
    }

    private function makeVariantProductWithDownloads(array $downloads = [])
    {
        $collection = tap(Collection::make('products'))->save();

        $collection->entryBlueprint()->ensureField('product_variants', [
            'type' => 'product_variants',
            'option_fields' => [
                ['handle' => 'downloads', 'field' => ['type' => 'assets']],
            ],
        ])->save();

        $product = Entry::make()
            ->id('digital-product')
            ->slug('digital-product')
            ->collection('products')
            ->data([
                'type' => 'digital',
                'product_variants' => [
                    'variants' => [
                        ['name' => 'Edition', 'values' => ['Standard']],
                    ],
                    'options' => [
                        ['key' => 'Standard', 'variant' => 'Standard', 'price' => 1000, 'downloads' => $downloads],
                    ],
                ],
            ]);

        $product->save();

        return $product;
    }

    private function makeOrderWithLineItem($product, array $lineItem = [])
    {
        $order = Order::make()
            ->id('order-id')
            ->lineItems([
                ['id' => 'line-item-id', 'product' => $product->id(), 'download_count' => 0, ...$lineItem],
            ]);

        $order->save();

        return $order;
    }

    private function generateDownloadUrl(string $orderId, string $lineItemId): string
    {
        return URL::signedRoute('statamic.cargo.download', [
            'orderId' => $orderId,
            'lineItem' => $lineItemId,
        ]);
    }
}
