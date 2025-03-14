<?php

namespace DuncanMcClean\Cargo\Exceptions;

class PreventCheckout extends \Exception
{
    public function errors(): array
    {
        return [
            'checkout' => $this->getMessage(),
        ];
    }
}
