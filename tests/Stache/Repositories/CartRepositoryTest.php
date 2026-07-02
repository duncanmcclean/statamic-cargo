<?php

namespace Tests\Stache\Repositories;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $this->repo = $this->app->make(\DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class);
    }

    #[Test]
    public function can_find_carts()
    {
        Cart::make()
            ->id('abc')
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->data(['foo' => 'bar'])
            ->lineItems([[
                'id' => '123',
                'product' => 'abc',
                'quantity' => 1,
                'unit_price' => 2500,
                'sub_total' => 2500,
                'tax_total' => 0,
                'total' => 2500,
            ]])
            ->save();

        $cart = $this->repo->find('abc');

        $this->assertEquals('abc', $cart->id());
        $this->assertEquals('default', $cart->site()->handle());
        $this->assertEquals('CJ Cregg', $cart->customer()->name());
        $this->assertEquals('cj.cregg@whitehouse.gov', $cart->customer()->email());
        $this->assertEquals(2500, $cart->grandTotal());
        $this->assertEquals(2500, $cart->subTotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(0, $cart->taxTotal());
        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(['foo' => 'bar'], $cart->data()->except('updated_at', 'fingerprint')->all());

        $this->assertEquals('123', $cart->lineItems()->first()->id());
        $this->assertEquals(2500, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function can_save_a_cart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $cart = Cart::make()
            ->id('abc')
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->lineItems([['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $this->repo->save($cart);

        $this->assertStringContainsString('content/cargo/carts/abc.yaml', $cart->path());

        $yaml = YAML::file($cart->path())->parse();

        $this->assertEquals('abc', $yaml['id']);
        $this->assertEquals(2500, $yaml['grand_total']);
        $this->assertEquals('bar', $yaml['foo']);

        $this->assertEquals([
            'id' => '123',
            'product' => 'abc',
            'quantity' => 1,
            'total' => 2500,
        ], $yaml['line_items'][0]);
    }

    /**
     * @see https://github.com/duncanmcclean/statamic-cargo/issues/240
     */
    #[Test]
    public function it_determines_the_cart_site_from_the_referer_header()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'nl' => ['name' => 'Dutch', 'locale' => 'nl_NL', 'url' => 'http://test.com/nl/'],
        ]);

        $request = Request::create('http://test.com/', 'GET');
        $request->headers->set('referer', 'http://test.com/nl/products');
        $this->app->instance('request', $request);
        $this->repo->forgetCurrentCart();

        $cart = $this->repo->current();

        $this->assertEquals('nl', $cart->site()->handle());
    }

    /**
     * @see https://github.com/duncanmcclean/statamic-cargo/issues/240
     */
    #[Test]
    public function resolving_the_current_cart_does_not_corrupt_the_current_site()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'nl' => ['name' => 'Dutch', 'locale' => 'nl_NL', 'url' => 'http://test.com/nl/'],
        ]);

        // We're on the English site, having navigated here from a page on the Dutch site.
        $request = Request::create('http://test.com/', 'GET');
        $request->headers->set('referer', 'http://test.com/nl/language-switcher');
        $this->app->instance('request', $request);
        $this->repo->forgetCurrentCart();

        $this->assertEquals('en', Site::current()->handle());

        // Rendering the cart (eg. a cart partial in the layout) must not repoint
        // Site::current() at the referer header for the rest of the request.
        $this->repo->current();

        $this->assertEquals('en', Site::current()->handle());
    }

    #[Test]
    public function can_delete_a_cart()
    {
        $cart = Cart::make()
            ->id('123')
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']);

        $cart->save();

        $this->assertFileExists($cart->path());

        $this->repo->delete($cart);

        $this->assertFileDoesNotExist($cart->path());
    }
}
