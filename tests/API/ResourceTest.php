<?php

namespace Tests\API;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Http\Resources\API\CartResource;
use DuncanMcClean\Cargo\Http\Resources\API\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\JsonResourceException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    #[DataProvider('cartEndpointsProvider')]
    public function cart_endpoints_use_the_default_cart_resource($method, $uri, $params)
    {
        $cart = $this->makeCart();

        $this
            ->{$method}($uri, $params)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer',
                    'grand_total',
                    'line_items',
                ],
            ])
            ->assertJsonPath('data.id', $cart->id());
    }

    #[Test]
    #[DataProvider('cartEndpointsProvider')]
    public function cart_endpoints_use_a_mapped_cart_resource($method, $uri, $params)
    {
        Resource::map([
            CartResource::class => CustomCartResource::class,
        ]);

        $cart = $this->makeCart();

        $this
            ->{$method}($uri, $params)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $cart->id(),
                    'custom' => true,
                ],
            ])
            ->assertJsonMissingPath('data.line_items');
    }

    public static function cartEndpointsProvider(): array
    {
        return [
            'cart' => ['getJson', '/!/cargo/cart', []],
            'add line item' => ['postJson', '/!/cargo/cart/line-items', ['product' => 'product-2', 'quantity' => 1]],
            'update line item' => ['patchJson', '/!/cargo/cart/line-items/line-item-1', ['quantity' => 2]],
            'remove line item' => ['deleteJson', '/!/cargo/cart/line-items/line-item-1', []],
        ];
    }

    #[Test]
    public function it_throws_when_mapping_an_invalid_resource()
    {
        $this->expectException(JsonResourceException::class);
        $this->expectExceptionMessage('[stdClass] is not a valid Cargo API resource');

        Resource::map([
            \stdClass::class => CustomCartResource::class,
        ]);
    }

    #[Test]
    public function it_throws_when_mapping_a_class_which_is_not_a_json_resource()
    {
        $this->expectException(JsonResourceException::class);
        $this->expectExceptionMessage('[stdClass] must be a subclass of '.JsonResource::class);

        Resource::map([
            CartResource::class => \stdClass::class,
        ]);
    }

    private function makeCart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1', 'price' => 1000])->save();
        Entry::make()->collection('products')->id('product-2')->data(['title' => 'Product 2', 'price' => 500])->save();

        $cart = Cart::make()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([
                [
                    'id' => 'line-item-1',
                    'product' => 'product-1',
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ]);

        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}

class CustomCartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id(),
            'custom' => true,
        ];
    }
}
