<?php

namespace Tests\Taxes;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Facades\TaxZone;
use DuncanMcClean\Cargo\Taxes\GetTaxRates;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTaxRatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $path = base_path('content/cargo/tax-zones.yaml');

        File::delete($path);
        File::ensureDirectoryExists(Str::beforeLast($path, '/'));
    }

    #[Test]
    public function can_get_tax_rates_by_everywhere()
    {
        $cart = Cart::make()->data([
            'shipping_address' => [
                'line_1' => '123 Fake St',
                'city' => 'Fakeville',
                'postcode' => 'FA 1234',
                'country' => 'USA',
                'state' => 'CA',
            ],
        ]);

        $taxClass = tap(TaxClass::make()->handle('standard'))->save();

        TaxZone::make()->handle('international')->data([
            'type' => 'everywhere',
            'rates' => ['standard' => 12],
        ])->save();

        $taxRates = (new GetTaxRates)($cart->shippingAddress(), $taxClass);

        $this->assertEquals([
            'international' => 12,
        ], $taxRates->all());
    }

    #[Test]
    public function can_get_tax_rates_by_country()
    {
        $cart = Cart::make()->data([
            'shipping_address' => [
                'line_1' => '123 Fake St',
                'city' => 'Fakeville',
                'postcode' => 'FA 1234',
                'country' => 'USA',
                'state' => 'CA',
            ],
        ]);

        $taxClass = tap(TaxClass::make()->handle('standard'))->save();

        TaxZone::make()->handle('usa')->data([
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 12],
        ])->save();

        $taxRates = (new GetTaxRates)($cart->shippingAddress(), $taxClass);

        $this->assertEquals([
            'usa' => 12,
        ], $taxRates->all());
    }

    #[Test]
    public function can_get_tax_rates_by_state()
    {
        $cart = Cart::make()->data([
            'shipping_address' => [
                'line_1' => '123 Fake St',
                'city' => 'Fakeville',
                'postcode' => 'FA 1234',
                'country' => 'USA',
                'state' => 'CA',
            ],
        ]);

        $taxClass = tap(TaxClass::make()->handle('standard'))->save();

        TaxZone::make()->handle('cal_flor_newy')->data([
            'type' => 'states',
            'countries' => ['USA'],
            'states' => ['CA', 'FL', 'NY'],
            'rates' => ['standard' => 12],
        ])->save();

        $taxRates = (new GetTaxRates)($cart->shippingAddress(), $taxClass);

        $this->assertEquals([
            'cal_flor_newy' => 12,
        ], $taxRates->all());
    }

    #[Test]
    public function can_get_tax_rates_by_postcode()
    {
        $cart = Cart::make()->data([
            'shipping_address' => [
                'line_1' => '123 Fake St',
                'city' => 'Fakeville',
                'postcode' => 'FA 1234',
                'country' => 'USA',
                'state' => 'CA',
            ],
        ]);

        $taxClass = tap(TaxClass::make()->handle('standard'))->save();

        TaxZone::make()->handle('postcodes')->data([
            'type' => 'postcodes',
            'countries' => ['USA'],
            'postcodes' => ['FA 1234', 'NY 4567'],
            'rates' => ['standard' => 12],
        ])->save();

        $taxRates = (new GetTaxRates)($cart->shippingAddress(), $taxClass);

        $this->assertEquals([
            'postcodes' => 12,
        ], $taxRates->all());
    }

    #[Test]
    public function can_get_tax_rates_by_postcode_with_wildcard()
    {
        $cart = Cart::make()->data([
            'shipping_address' => [
                'line_1' => '123 Fake St',
                'city' => 'Fakeville',
                'postcode' => 'FA 1234',
                'country' => 'USA',
                'state' => 'CA',
            ],
        ]);

        $taxClass = tap(TaxClass::make()->handle('standard'))->save();

        TaxZone::make()->handle('postcodes')->data([
            'type' => 'postcodes',
            'countries' => ['USA'],
            'postcodes' => ['FA 1*'],
            'rates' => ['standard' => 12],
        ])->save();

        $taxRates = (new GetTaxRates)($cart->shippingAddress(), $taxClass);

        $this->assertEquals([
            'postcodes' => 12,
        ], $taxRates->all());
    }

    #[Test]
    public function can_get_multiple_tax_rates()
    {
        $cart = Cart::make()->data([
            'shipping_address' => [
                'line_1' => '123 Fake St',
                'city' => 'Fakeville',
                'postcode' => 'FA 1234',
                'country' => 'USA',
                'state' => 'CA',
            ],
        ]);

        $taxClass = tap(TaxClass::make()->handle('standard'))->save();

        TaxZone::make()->handle('usa')->data([
            'type' => 'countries',
            'countries' => ['USA'],
            'rates' => ['standard' => 12],
        ])->save();

        TaxZone::make()->handle('california')->data([
            'type' => 'states',
            'countries' => ['USA'],
            'states' => ['CA'],
            'rates' => ['standard' => 5],
        ])->save();

        TaxZone::make()->handle('postcodes')->data([
            'type' => 'postcodes',
            'countries' => ['USA'],
            'postcodes' => ['FA*'],
            'rates' => ['standard' => 2],
        ])->save();

        $taxRates = (new GetTaxRates)($cart->shippingAddress(), $taxClass);

        $this->assertEquals([
            'usa' => 12,
            'california' => 5,
            'postcodes' => 2,
        ], $taxRates->all());
    }
}
