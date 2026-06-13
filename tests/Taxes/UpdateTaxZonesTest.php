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

    #[Test]
    public function can_update_tax_zone_with_numeric_tax_class_handle()
    {
        TaxClass::make()->handle('21')->set('title', '21%')->save();

        $taxZone = tap(TaxZone::make()->handle('netherlands')->data([
            'title' => 'Netherlands',
            'type' => 'countries',
            'countries' => ['NL'],
            'rates' => ['21' => 21],
        ]))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.tax-zones.update', $taxZone->handle()), [
                'title' => 'Netherlands',
                'type' => 'countries',
                'countries' => ['NL'],
                'rates' => ['21' => 19],
            ])
            ->assertOk();

        $taxZone = TaxZone::find('netherlands');
        $this->assertEquals(['21' => 19], $taxZone->get('rates'));
    }
}
