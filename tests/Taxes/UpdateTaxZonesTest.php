<?php

namespace Tests\Taxes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateTaxZonesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/cargo/tax-classes.yaml'));
        File::delete(base_path('content/cargo/tax-zones.yaml'));
    }

    #[Test]
    public function can_update_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('title', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('title', 'Reduced')->save();

        $taxZone = tap(TaxZone::make()->handle('united-kingdom')->data([
            'title' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GB'],
            'rates' => ['standard' => 20, 'reduced' => 5],
        ]))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.tax-zones.update', $taxZone->handle()), [
                'title' => 'United Kingdom',
                'type' => 'countries',
                'countries' => ['GB'],
                'rates' => [
                    'standard' => 25,
                    'reduced' => 5.5,
                ],
            ])
            ->assertOk();

        $taxZone = TaxZone::find('united-kingdom');
        $this->assertEquals(['standard' => 25, 'reduced' => 5.5], $taxZone->get('rates'));
    }

    #[Test]
    public function cant_update_tax_zone_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        TaxClass::make()->handle('standard')->set('title', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('title', 'Reduced')->save();

        $taxZone = tap(TaxZone::make()->handle('united-kingdom')->data([
            'title' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GB'],
            'rates' => ['standard' => 20, 'reduced' => 5],
        ]))->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('cargo.tax-zones.update', $taxZone->handle()), [
                'title' => 'United Kingdom',
                'type' => 'countries',
                'countries' => ['GB'],
                'rates' => [
                    'standard' => 25,
                    'reduced' => 5.5,
                ],
            ])
            ->assertRedirect('/cp');

        $taxZone = TaxZone::find('united-kingdom');
        $this->assertEquals(['standard' => 20, 'reduced' => 5], $taxZone->get('rates'));
    }
}
