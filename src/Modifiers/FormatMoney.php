<?php

namespace DuncanMcClean\Cargo\Modifiers;

use DuncanMcClean\Cargo\Support\Money;
use Statamic\Facades\Site;
use Statamic\Modifiers\Modifier;

class FormatMoney extends Modifier
{
    public function index($value, $params, $context)
    {
        if (! $value) {
            return $value;
        }

        if (! is_numeric($value)) {
            throw new \Exception('The format_money modifier requires a numeric value.');
        }

        return Money::format($value, Site::current());
    }
}
