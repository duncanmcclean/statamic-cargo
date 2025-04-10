<?php

namespace DuncanMcClean\Cargo\Actions;

use DuncanMcClean\Cargo\Contracts;
use Statamic\Actions\Action;

class Delete extends Action
{
    protected $dangerous = true;

    protected static $handle = 'cargo-delete';

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        switch (true) {
            case $item instanceof Contracts\Discounts\Discount:
                return true;
            default:
                return false;
        }
    }

    public function authorize($user, $item)
    {
        return $user->can('delete', $item);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }

    public function redirect($items, $values)
    {
        if ($this->context['view'] !== 'form') {
            return;
        }

        $item = $items->first();

        switch (true) {
            case $item instanceof Contracts\Discounts\Discount:
                return cp_route('cargo.discounts.index');
        }
    }
}
