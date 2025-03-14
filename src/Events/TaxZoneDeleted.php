<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxZoneDeleted implements ProvidesCommitMessage
{
    public function __construct(public TaxZone $taxZone) {}

    public function commitMessage()
    {
        return __('Tax Zone deleted', [], config('statamic.git.locale'));
    }
}
