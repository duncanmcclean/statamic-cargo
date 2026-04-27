<?php

namespace Tests\Taxes\Eloquent;

use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Taxes\Eloquent\TaxClassModel;
use DuncanMcClean\Cargo\Taxes\TaxClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;
use Tests\TestCase;

class TaxClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Taxes\TaxClassRepository::class,
            \DuncanMcClean\Cargo\Taxes\Eloquent\TaxClassRepository::class
        );
    }

    #[Test]
    public function it_can_get_all_tax_classes()
    {
        TaxClassModel::query()->create(['handle' => 'standard', 'data' => ['title' => 'Standard Tax']]);
        TaxClassModel::query()->create(['handle' => 'reduced', 'data' => ['title' => 'Reduced Tax']]);

        $all = Facades\TaxClass::all();

        $this->assertEquals(2, $all->count());
        $this->assertInstanceOf(Collection::class, $all);

        $this->assertEquals('Standard Tax', $all->first()->get('title'));
        $this->assertEquals('Reduced Tax', $all->last()->get('title'));
    }

    #[Test]
    public function it_can_find_a_tax_class()
    {
        TaxClassModel::query()->create(['handle' => 'standard', 'data' => ['title' => 'Standard Tax']]);
        TaxClassModel::query()->create(['handle' => 'reduced', 'data' => ['title' => 'Reduced Tax']]);

        $taxClass = Facades\TaxClass::find('standard');

        $this->assertInstanceOf(TaxClass::class, $taxClass);
        $this->assertEquals('standard', $taxClass->handle());
        $this->assertEquals('Standard Tax', $taxClass->get('title'));
    }

    #[Test]
    public function it_can_make_a_tax_class()
    {
        $taxClass = Facades\TaxClass::make();

        $this->assertInstanceOf(TaxClass::class, $taxClass);
    }

    #[Test]
    public function it_can_save_a_tax_class()
    {
        TaxClassModel::query()->create(['handle' => 'standard', 'data' => ['title' => 'Standard Tax']]);

        $taxClass = Facades\TaxClass::make()
            ->handle('reduced')
            ->data(['title' => 'Reduced Tax']);

        $save = $taxClass->save();

        $this->assertTrue($save);

        $this->assertDatabaseHas('cargo_tax_classes', ['handle' => 'standard']);
        $this->assertDatabaseHas('cargo_tax_classes', ['handle' => 'reduced']);
    }

    #[Test]
    public function it_can_delete_a_tax_class()
    {
        TaxClassModel::query()->create(['handle' => 'standard', 'data' => ['title' => 'Standard Tax']]);
        TaxClassModel::query()->create(['handle' => 'reduced', 'data' => ['title' => 'Reduced Tax']]);

        $delete = Facades\TaxClass::find('standard')->delete();

        $this->assertTrue($delete);

        $this->assertDatabaseMissing('cargo_tax_classes', ['handle' => 'standard']);
        $this->assertDatabaseHas('cargo_tax_classes', ['handle' => 'reduced']);
    }

    #[Test]
    public function it_handles_tax_classes_with_numerical_handles_correctly()
    {
        $taxClass = Facades\TaxClass::make()
            ->handle('21')
            ->data(['title' => '21% Tax']);

        $taxClass->save();

        $find = Facades\TaxClass::find('21');

        $this->assertInstanceOf(TaxClass::class, $find);
        $this->assertSame('21', $find->handle());
        $this->assertEquals('21% Tax', $find->get('title'));

        $all = Facades\TaxClass::all();

        $this->assertEquals(1, $all->count());
        $this->assertSame('21', $all->first()->handle());

        $find->set('title', '21% VAT')->save();

        $reloaded = Facades\TaxClass::find('21');
        $this->assertEquals('21% VAT', $reloaded->get('title'));

        $find->delete();

        $this->assertDatabaseMissing('cargo_tax_classes', ['handle' => '21']);
    }
}
