<?php

namespace Tests\Tags;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Shipping\FreeShipping;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class ShippingOptionsTagTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_outputs_available_shipping_options()
    {
        FreeShipping::class::register();
        FakeShippingMethod::register();

        Config::set('statamic.cargo.shipping.methods', [
            'free_shipping' => [],
            'fake_shipping_method' => [],
        ]);

        $cart = tap(Cart::make()->grandTotal(1000))->saveWithoutRecalculating();

        Cart::setCurrent($cart);

        $output = $this->tag('{{ shipping_options }}<option>{{ name }} ({{ price }})</option>{{ /shipping_options }}');

        // Free Shipping
        $this->assertStringContainsString('<option>Free Shipping (£0.00)</option>', $output);

        // Fake Shipping Method
        $this->assertStringContainsString('<option>In-Store Pickup (£0.00)</option>', $output);
        $this->assertStringContainsString('<option>Standard Shipping (£5.00)</option>', $output);
        $this->assertStringContainsString('<option>Express Shipping (£10.00)</option>', $output);
    }

    private function tag($tag, $variables = [])
    {
        return Parse::template($tag, $variables);
    }
}
