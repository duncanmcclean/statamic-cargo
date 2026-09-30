<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Orders\Blueprint as OrderBlueprint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    #[Test]
    public function custom_tabs_are_added_to_the_blueprint()
    {
        $this->customBlueprint([
            'gifting' => ['display' => 'Gifting', 'sections' => [['fields' => [
                ['handle' => 'gift_message', 'field' => ['type' => 'textarea']],
            ]]]],
        ]);

        $tab = (new OrderBlueprint)()->tabs()->get('gifting');

        $this->assertEquals('Gifting', $tab->display());
        $this->assertEquals(['gift_message'], $tab->fields()->all()->keys()->all());
    }

    #[Test]
    #[DataProvider('matchingSectionsProvider')]
    public function custom_fields_are_merged_into_the_matching_section(string $tabHandle, ?string $sectionDisplay, array $expectedFields)
    {
        $this->customBlueprint([
            $tabHandle => ['display' => 'Custom', 'sections' => [$this->customSection($sectionDisplay)]],
        ]);

        $section = (new OrderBlueprint)()->tabs()->get($tabHandle)->sections()->first(fn ($section) => $section->display() === $sectionDisplay);

        $this->assertEquals($expectedFields, $section->fields()->all()->keys()->all());
    }

    public static function matchingSectionsProvider(): array
    {
        return [
            'untitled details section' => ['details', null, ['status', 'order_number', 'line_items', 'custom_field']],
            'shipping address section' => ['shipping', 'Shipping Address', ['shipping_address', 'custom_field']],
            'billing address section' => ['payment', 'Billing Address', ['billing_address', 'custom_field']],
        ];
    }

    #[Test]
    #[DataProvider('newSectionsProvider')]
    public function custom_sections_are_appended_to_the_matching_tab(string $tabHandle, ?string $sectionDisplay)
    {
        $this->customBlueprint([
            $tabHandle => ['display' => 'Custom', 'sections' => [$this->customSection($sectionDisplay)]],
        ]);

        $section = (new OrderBlueprint)()->tabs()->get($tabHandle)->sections()->last();

        $this->assertEquals($sectionDisplay, $section->display());
        $this->assertEquals(['custom_field'], $section->fields()->all()->keys()->all());
    }

    public static function newSectionsProvider(): array
    {
        return [
            'untitled shipping section' => ['shipping', null],
            'untitled payment section' => ['payment', null],
            'new details section' => ['details', 'Gifting'],
            'new sidebar section' => ['sidebar', 'Gifting'],
        ];
    }

    private function customSection(?string $display): array
    {
        return array_filter([
            'display' => $display,
            'fields' => [['handle' => 'custom_field', 'field' => ['type' => 'text']]],
        ]);
    }

    private function customBlueprint(array $tabs): void
    {
        $customBlueprint = Blueprint::make('order')->setNamespace('cargo')->setContents(['tabs' => $tabs]);

        Blueprint::partialMock()->shouldReceive('find')->with('cargo::order')->andReturn($customBlueprint);
    }
}
