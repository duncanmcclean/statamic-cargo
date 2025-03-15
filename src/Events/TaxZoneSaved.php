<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class TaxZoneSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public TaxZone $taxZone) {}

    public function commitMessage()
    {
        return __('Tax Zone saved', [], config('statamic.git.locale'));
    }
}
