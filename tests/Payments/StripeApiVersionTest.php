<?php

namespace Tests\Payments;

use DuncanMcClean\Cargo\Payments\Gateways\Stripe;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Util\ApiVersion;
use Tests\TestCase;

#[Group('payments')]
class StripeApiVersionTest extends TestCase
{
    protected function tearDown(): void
    {
        \Stripe\Stripe::setApiVersion(ApiVersion::CURRENT);

        parent::tearDown();
    }

    #[Test]
    public function it_uses_the_configured_api_version()
    {
        config()->set('statamic.cargo.payments.gateways', ['stripe' => [
            'key' => 'pk_test_123',
            'secret' => 'sk_test_123',
            'version' => '2024-06-20',
        ]]);

        new Stripe;

        $this->assertSame('2024-06-20', \Stripe\Stripe::getApiVersion());
    }
}
