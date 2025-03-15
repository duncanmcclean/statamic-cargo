<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class TaxClassSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public TaxClass $taxClass) {}

    public function commitMessage()
    {
        return __('Tax Class saved', [], config('statamic.git.locale'));
    }
}
