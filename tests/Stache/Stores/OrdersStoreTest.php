<?php

namespace Tests\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;
use DuncanMcClean\Cargo\Facades;

class OrdersStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('orders');
        $this->store->directory($this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/cargo/orders'));
    }

    #[Test]
    public function it_makes_order_instances_from_files()
    {
        $item = $this->store->makeItemFromFile(
            $this->directory.'/2017-01-02-123450.12345.yaml',
            "id: foo\norder_number: 12345\nbar: baz",
        );

        $this->assertInstanceOf(Order::class, $item);
        $this->assertEquals('foo', $item->id());
        $this->assertEquals('12345', $item->orderNumber());
        $this->assertEquals('2017-01-02 12:34:50', $item->date()->format('Y-m-d H:i:s'));
        $this->assertEquals('baz', $item->get('bar'));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $order = Facades\Order::make()
            ->id('123')
            ->orderNumber('12345')
            ->date('2017-07-04');

        $this->store->save($order);

        $this->assertStringEqualsFile($path = $this->directory.'/2017-07-04.12345.yaml', $order->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->store->paths()->get('123'));
    }

    #[Test]
    public function it_saves_to_disk_with_modified_path()
    {
        $order = Facades\Order::make()
            ->id('123')
            ->orderNumber('12345')
            ->date('2017-07-04');

        $this->store->save($order);

        $this->assertStringEqualsFile($initialPath = $this->directory.'/2017-07-04.12345.yaml', $order->fileContents());
        $this->assertEquals($initialPath, $this->store->paths()->get('123'));

        $order->orderNumber('99999');
        $order->save();

        $this->assertStringEqualsFile($path = $this->directory.'/2017-07-04.99999.yaml', $order->fileContents());
        $this->assertEquals($path, $this->store->paths()->get('123'));

        @unlink($initialPath);
        @unlink($path);
    }
}