<?php

namespace Tests\Taxes\Eloquent;

use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Taxes\Eloquent\TaxZoneModel;
use DuncanMcClean\Cargo\Taxes\TaxZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;
use Tests\TestCase;

class TaxZoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Taxes\TaxZoneRepository::class,
            \DuncanMcClean\Cargo\Taxes\Eloquent\TaxZoneRepository::class
        );
    }

    #[Test]
    public function it_can_get_all_tax_zones()
    {
        TaxZoneModel::query()->create([
            'handle' => 'uk',
            'data' => ['title' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
        ]);
        TaxZoneModel::query()->create([
            'handle' => 'eu',
            'data' => ['title' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ]);

        $all = Facades\TaxZone::all();

        $this->assertEquals(2, $all->count());
        $this->assertInstanceOf(Collection::class, $all);

        $this->assertEquals('United Kingdom', $all->first()->get('title'));
        $this->assertEquals('European Union', $all->last()->get('title'));
    }

    #[Test]
    public function it_can_find_a_tax_zone()
    {
        TaxZoneModel::query()->create([
            'handle' => 'uk',
            'data' => ['title' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
        ]);
        TaxZoneModel::query()->create([
            'handle' => 'eu',
            'data' => ['title' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ]);

        $taxZone = Facades\TaxZone::find('uk');

        $this->assertInstanceOf(TaxZone::class, $taxZone);
        $this->assertEquals('uk', $taxZone->handle());
        $this->assertEquals('United Kingdom', $taxZone->get('title'));
        $this->assertEquals('countries', $taxZone->get('type'));
        $this->assertEquals(20, $taxZone->rates()->get('standard'));
    }

    #[Test]
    public function it_can_make_a_tax_zone()
    {
        $taxZone = Facades\TaxZone::make();

        $this->assertInstanceOf(TaxZone::class, $taxZone);
    }

    #[Test]
    public function it_can_save_a_tax_zone()
    {
        TaxZoneModel::query()->create([
            'handle' => 'uk',
            'data' => ['title' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
        ]);

        $taxZone = Facades\TaxZone::make()
            ->handle('eu')
            ->data([
                'title' => 'European Union',
                'type' => 'countries',
                'countries' => ['FRA', 'DEU'],
                'rates' => ['standard' => 20],
            ]);

        $save = $taxZone->save();

        $this->assertTrue($save);

        $this->assertDatabaseHas('cargo_tax_zones', ['handle' => 'uk']);
        $this->assertDatabaseHas('cargo_tax_zones', ['handle' => 'eu']);
    }

    #[Test]
    public function it_can_delete_a_tax_zone()
    {
        TaxZoneModel::query()->create([
            'handle' => 'uk',
            'data' => ['title' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
        ]);
        TaxZoneModel::query()->create([
            'handle' => 'eu',
            'data' => ['title' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ]);

        $delete = Facades\TaxZone::find('uk')->delete();

        $this->assertTrue($delete);

        $this->assertDatabaseMissing('cargo_tax_zones', ['handle' => 'uk']);
        $this->assertDatabaseHas('cargo_tax_zones', ['handle' => 'eu']);
    }

    #[Test]
    public function it_handles_tax_zones_with_numerical_tax_class_handles_correctly()
    {
        TaxZoneModel::query()->create([
            'handle' => 'uk',
            'data' => [
                'title' => 'United Kingdom',
                'type' => 'countries',
                'countries' => ['GBR'],
                'rates' => [
                    'standard' => 20,
                    21 => 15,
                ],
            ],
        ]);

        $taxZone = Facades\TaxZone::find('uk');
        $rates = $taxZone->rates();

        $this->assertEquals(20, $rates->get('standard'));
        $this->assertEquals(15, $rates->get('21'));
    }

    #[Test]
    public function it_handles_tax_zones_with_numerical_handles_correctly()
    {
        $taxZone = Facades\TaxZone::make()
            ->handle('21')
            ->data([
                'title' => '21% VAT Zone',
                'type' => 'everywhere',
                'rates' => ['standard' => 21],
            ]);

        $taxZone->save();

        $find = Facades\TaxZone::find('21');

        $this->assertInstanceOf(TaxZone::class, $find);
        $this->assertSame('21', $find->handle());
        $this->assertEquals('21% VAT Zone', $find->get('title'));

        $all = Facades\TaxZone::all();

        $this->assertEquals(1, $all->count());
        $this->assertSame('21', $all->first()->handle());

        $find->set('title', '21% VAT')->save();

        $reloaded = Facades\TaxZone::find('21');
        $this->assertEquals('21% VAT', $reloaded->get('title'));

        $find->delete();

        $this->assertDatabaseMissing('cargo_tax_zones', ['handle' => '21']);
    }
}
