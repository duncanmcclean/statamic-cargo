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

class EditTaxZonesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/cargo/tax-classes.yaml'));
        File::delete(base_path('content/cargo/tax-zones.yaml'));
    }

    #[Test]
    public function can_edit_tax_zone()
    {
        TaxClass::make()->handle('standard')->set('title', 'Standard')->save();

        $taxZone = tap(TaxZone::make()->handle('united-kingdom')->data([
            'title' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GB'],
            'rates' => ['standard' => 20],
        ]))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.tax-zones.edit', $taxZone->handle()))
            ->assertOk()
            ->assertSee('United Kingdom');
    }

    #[Test]
    public function cant_edit_tax_zone_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        TaxClass::make()->handle('standard')->set('title', 'Standard')->save();

        $taxZone = tap(TaxZone::make()->handle('united-kingdom')->data([
            'title' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GB'],
            'rates' => ['standard' => 20],
        ]))->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.tax-zones.edit', $taxZone->handle()))
            ->assertRedirect('/cp');
    }

    #[Test]
    public function can_edit_tax_zone_with_numeric_tax_class_handle()
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
            ->get(cp_route('cargo.tax-zones.edit', $taxZone->handle()))
            ->assertOk()
            ->assertSee('Netherlands');
    }
}
