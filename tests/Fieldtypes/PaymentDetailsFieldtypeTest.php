<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Fieldtypes\PaymentDetails;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Tests\TestCase;

class PaymentDetailsFieldtypeTest extends TestCase
{
    #[Test]
    public function it_returns_a_link_to_the_payment_in_the_stripe_dashboard()
    {
        config()->set('statamic.cargo.payments.gateways', ['stripe' => ['key' => 'pk_test', 'secret' => 'sk_test']]);

        $order = Order::make()->set('payment_gateway', 'stripe')->set('stripe_payment_intent', 'pi_123');

        $fieldtype = (new PaymentDetails)->setField((new Field('payment_details', ['type' => 'payment_details']))->setParent($order));

        $this->assertEquals([
            'text' => 'pi_123',
            'url' => 'https://dashboard.stripe.com/payments/pi_123',
        ], $fieldtype->preProcess(null)['details']['Payment ID']);
    }
}
