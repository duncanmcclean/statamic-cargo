<?php

namespace Tests\Taxes;

use DuncanMcClean\Cargo\Contracts\Taxes\Driver;
use DuncanMcClean\Cargo\Taxes\TaxDriver;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CustomTaxDriverTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->bind(Driver::class, fn () => new class extends TaxDriver
        {
            public function getBreakdown(int $total): SupportCollection
            {
                return collect();
            }
        });
    }

    #[Test]
    public function tax_classes_are_available()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.tax-classes.index'))
            ->assertOk()
            ->assertSee('Tax Classes');
    }

    #[Test]
    public function tax_class_field_is_added_to_product_blueprint()
    {
        Collection::make('products')->save();

        $blueprint = Entry::make()->collection('products')->blueprint();

        $this->assertTrue($blueprint->hasField('tax_class'));
    }

    #[Test]
    public function tax_zones_are_unavailable()
    {
        $this->assertFalse(Route::has('statamic.cp.cargo.tax-zones.index'));
    }
}
