<?php

namespace Query\Scopes\Filters;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Query\Scopes\Filters\Fields;
use DuncanMcClean\Cargo\Query\Scopes\Filters\Fields\PaymentGateways;
use DuncanMcClean\Cargo\Query\Scopes\Filters\Fields\ShippingMethods;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class FieldtypeFilterTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        FakeShippingMethod::register();

        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);
        config()->set('statamic.cargo.payments.gateways', ['dummy' => []]);
    }

    #[Test]
    public function payment_gateway_field_uses_the_payment_gateways_filter()
    {
        $filter = Order::blueprint()->field('payment_gateway')->fieldtype()->filter();

        $this->assertInstanceOf(PaymentGateways::class, $filter);
        $this->assertArrayHasKey('dummy', $filter->fieldItems()['value']['options']);
    }

    #[Test]
    public function can_filter_orders_by_payment_gateway()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();

        Order::make()->id('123')->cart('abc')->merge(['payment_gateway' => 'dummy'])->save();
        Order::make()->id('456')->cart('def')->merge(['payment_gateway' => 'stripe'])->save();

        $query = Order::query();

        (new Fields)->apply($query, ['payment_gateway' => ['operator' => '=', 'value' => 'dummy']]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals([123], $results->map->id()->all());
    }

    #[Test]
    public function shipping_method_field_uses_the_shipping_methods_filter()
    {
        $filter = Order::blueprint()->field('shipping_method')->fieldtype()->filter();

        $this->assertInstanceOf(ShippingMethods::class, $filter);
        $this->assertArrayHasKey('fake_shipping_method', $filter->fieldItems()['value']['options']);
    }

    #[Test]
    public function can_filter_orders_by_shipping_method()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();

        Order::make()->id('123')->cart('abc')->merge(['shipping_method' => 'fake_shipping_method'])->save();
        Order::make()->id('456')->cart('def')->merge(['shipping_method' => 'something_else'])->save();

        $query = Order::query();

        (new Fields)->apply($query, ['shipping_method' => ['operator' => '=', 'value' => 'fake_shipping_method']]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals([123], $results->map->id()->all());
    }
}
