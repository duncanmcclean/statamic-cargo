<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Tests\TestCase;

class TaxClassesFieldtypeTest extends TestCase
{
    protected function validate(mixed $value, array $config = []): void
    {
        Blueprint::makeFromFields([
            'tax_class' => array_merge(['type' => 'tax_classes', 'max_items' => 1], $config),
        ])
            ->fields()
            ->addValues(['tax_class' => $value])
            ->validator()
            ->validate();
    }

    #[Test]
    public function an_empty_optional_field_passes_validation()
    {
        $this->validate([]);
        $this->validate(null);

        $this->addToAssertionCount(2);
    }

    #[Test]
    public function an_empty_required_field_fails_validation()
    {
        $this->expectException(ValidationException::class);

        $this->validate([], ['validate' => ['required']]);
    }

    #[Test]
    public function an_existing_tax_class_passes_validation()
    {
        TaxClass::make()->handle('general')->set('title', 'General')->save();

        $this->validate(['general']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function a_missing_tax_class_fails_validation()
    {
        $this->expectException(ValidationException::class);

        $this->validate(['nope']);
    }
}
