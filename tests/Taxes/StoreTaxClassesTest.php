<?php

namespace Tests\Taxes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreTaxClassesTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('content/cargo/tax-classes.yaml'));
    }

    #[Test]
    public function can_store_tax_class()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.tax-classes.store'), [
                'title' => 'Standard',
            ])
            ->assertOk()
            ->assertJson(['redirect' => cp_route('cargo.tax-classes.edit', 'standard')]);

        $taxClass = TaxClass::find('standard');
        $this->assertEquals('Standard', $taxClass->get('title'));
    }

    #[Test]
    public function cant_store_tax_class_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->post(cp_route('cargo.tax-classes.store'), [
                'title' => 'Standard',
            ])
            ->assertRedirect('/cp');

        $this->assertNull(TaxClass::find('standard'));
    }
}
