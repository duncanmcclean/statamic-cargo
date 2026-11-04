<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Fieldtypes\ShippingDetails;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class ShippingDetailsFieldtypeTest extends TestCase
{
    #[Test]
    public function it_escapes_details()
    {
        FakeShippingMethod::register();

        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);

        $order = Order::make()
            ->set('shipping_method', 'fake_shipping_method')
            ->set('shipping_option', ['name' => 'Standard Shipping', 'handle' => 'standard_shipping', 'price' => 500])
            ->set('tracking_number', '<img src=x onerror=alert(document.cookie)>');

        $fieldtype = (new ShippingDetails)->setField((new Field('shipping_details', ['type' => 'shipping_details']))->setParent($order));

        $this->assertEquals(
            '&lt;img src=x onerror=alert(document.cookie)&gt;',
            $fieldtype->preProcess(null)['details']['Tracking Number']
        );
    }
}
