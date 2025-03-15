<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class TaxClassDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public TaxClass $taxClass) {}

    public function commitMessage()
    {
        return __('Tax Class deleted', [], config('statamic.git.locale'));
    }
}
