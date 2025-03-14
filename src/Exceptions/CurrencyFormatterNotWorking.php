<?php

namespace DuncanMcClean\Cargo\Exceptions;

use Exception;

class CurrencyFormatterNotWorking extends Exception
{
    public function __construct()
    {
        parent::__construct('The PHP-intl extension is missing.');
    }
}
