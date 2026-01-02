<?php

namespace Tests\Tags;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class PaymentGatewayTagTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_outputs_available_payment_gateways()
    {
        Config::set('statamic.cargo.payments.gateways', [
            'dummy' => [],
            'pay_on_delivery' => [],
        ]);

        $cart = tap(Cart::make()->grandTotal(1000))->saveWithoutRecalculating();

        Cart::setCurrent($cart);

        $output = $this->tag('{{ payment_gateways }}<option>{{ name }}</option>{{ /payment_gateways }}');

        $this->assertStringContainsString('<option>Dummy</option>', $output);
        $this->assertStringNotContainsString('<option>Pay on delivery</option>', $output);
    }

    private function tag($tag, $variables = [])
    {
        return Parse::template($tag, $variables);
    }
}
