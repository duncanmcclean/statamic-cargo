<?php

namespace Tests\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;
use DuncanMcClean\Cargo\Facades;

class CartsStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('carts');
        $this->store->directory($this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/cargo/carts'));
    }

    #[Test]
    public function it_makes_cart_instances_from_files()
    {
        $item = $this->store->makeItemFromFile(
            $this->directory.'/foo.yaml',
            "id: foo\ngrand_total: 2599\nbar: baz",
        );

        $this->assertInstanceOf(Cart::class, $item);
        $this->assertEquals('foo', $item->id());
        $this->assertEquals(2599, $item->grandTotal());
        $this->assertEquals('baz', $item->get('bar'));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $cart = Facades\Cart::make()->id('123');

        $this->store->save($cart);

        $this->assertStringEqualsFile($path = $this->directory.'/123.yaml', $cart->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->store->paths()->get('123'));
    }
}