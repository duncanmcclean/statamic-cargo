<?php

namespace Data;

use DuncanMcClean\Cargo\Data\Address;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Item;
use Tests\TestCase;

class AddressTest extends TestCase
{
    #[Test]
    public function can_get_country()
    {
        $address = Address::make([
            'name' => 'Cosmo Kramer',
            'line_1' => '129 W 81st Street',
            'line_2' => 'Apartment 5B',
            'city' => 'New York',
            'postcode' => 10024,
            'country' => 'USA',
            'state' => 'NY',
        ]);

        $country = $address->country();

        $this->assertInstanceOf(Item::class, $country);
        $this->assertEquals($country->value(), 'USA');
        $this->assertEquals($country->extra()['name'], 'United States');
    }

    #[Test]
    public function can_get_state()
    {
        $address = Address::make([
            'name' => 'Cosmo Kramer',
            'line_1' => '129 W 81st Street',
            'line_2' => 'Apartment 5B',
            'city' => 'New York',
            'postcode' => 10024,
            'country' => 'USA',
            'state' => 'NY',
        ]);

        $state = $address->state();

        $this->assertIsArray($state);
        $this->assertEquals([
            'code' => 'NY',
            'name' => 'New York',
        ], $state);
    }

    #[Test]
    public function can_convert_address_to_string()
    {
        $address = Address::make([
            'name' => 'Cosmo Kramer',
            'line_1' => '129 W 81st Street',
            'line_2' => 'Apartment 5B',
            'city' => 'New York',
            'postcode' => 10024,
            'country' => 'USA',
            'state' => 'NY',
        ]);

        $this->assertEquals('Cosmo Kramer, 129 W 81st Street, Apartment 5B, New York, 10024, United States, New York', $address->__toString());
    }

    #[Test]
    public function can_add_fields_via_hook()
    {
        Address::hook('fields', function ($payload, $next) {
            $fields = $payload->fields;
            $fields['first_name'] = ['type', 'text', 'display' => 'First Name'];
            $fields['last_name'] = ['type', 'text', 'display' => 'Last Name'];
            $fields['company'] = ['type', 'text', 'display' => 'Company'];
            $payload->fields = $fields;

            return $next($payload);
        });

        $blueprint = Address::blueprint();

        $this->assertTrue($blueprint->hasField('first_name'));
        $this->assertTrue($blueprint->hasField('last_name'));
        $this->assertTrue($blueprint->hasField('company'));
    }
}