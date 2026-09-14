<?php

namespace Tests\Data;

use DuncanMcClean\Cargo\Data\States;
use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatesTest extends TestCase
{
    #[Test]
    public function can_get_states_by_country()
    {
        $states = States::byCountry('DEU');

        $this->assertCount(16, $states);
        $this->assertEquals(['name' => 'Bavaria', 'code' => 'BY'], $states->firstWhere('code', 'BY'));
    }

    #[Test]
    public function returns_no_states_without_a_country()
    {
        $this->assertTrue(States::byCountry(null)->isEmpty());
        $this->assertTrue(States::byCountry('XYZ')->isEmpty());
    }

    #[Test]
    public function state_names_are_translated()
    {
        app()->setLocale('de');

        $this->assertEquals('Bayern', States::byCountry('DEU')->firstWhere('code', 'BY')['name']);
        $this->assertEquals('Wien', States::byCountry('AUT')->firstWhere('code', '9')['name']);
        $this->assertEquals('New York', States::byCountry('USA')->firstWhere('code', 'NY')['name']);
    }

    #[Test]
    public function states_are_sorted_by_translated_name()
    {
        app()->setLocale('de');

        $this->assertEquals(
            ['Hessen', 'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein-Westfalen'],
            States::byCountry('DEU')->pluck('name')->slice(6, 4)->values()->all()
        );
    }

    #[Test]
    public function state_names_can_be_overridden()
    {
        Lang::addLines(['states.USA.NY' => 'The Big Apple'], 'en', 'cargo');

        $this->assertEquals('The Big Apple', States::byCountry('USA')->firstWhere('code', 'NY')['name']);
    }
}
