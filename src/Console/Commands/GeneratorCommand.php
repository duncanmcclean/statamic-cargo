<?php

namespace DuncanMcClean\Cargo\Console\Commands;

use Statamic\Console\Commands\GeneratorCommand as StatamicGeneratorCommand;
use Statamic\Support\Str;

abstract class GeneratorCommand extends StatamicGeneratorCommand
{
    protected function getStub($stub = null)
    {
        $stub = $stub ?? $this->stub;

        return __DIR__.'/stubs/'.$stub;
    }

    /**
     * We want to override the "type" displayed in the success message to be more user-friendly.
     *
     * However, we would need to override the handle() method to do this, but then we need to copy
     * the entire handle() method from the parent class, which is not ideal.
     *
     * Instead, we're overriding this method, which is the last method called before the success
     * message is displayed and modifying the type here.
     */
    protected function sortImports($stub)
    {
        $sortImports = parent::sortImports($stub);

        $this->type = $this->prepareTypeForDisplay();

        return $sortImports;
    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => [
                'What should the '.$this->prepareTypeForDisplay().' be named?',
                match ($this->type) {
                    'PaymentGateway' => 'E.g. Stripe',
                    'ShippingMethod' => 'E.g. FreeShipping',
                    default => '',
                },
            ],
        ];
    }

    protected function prepareTypeForDisplay(): string
    {
        return Str::headline($this->type);
    }
}