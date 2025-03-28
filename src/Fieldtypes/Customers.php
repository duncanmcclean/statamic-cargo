<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Customers\GuestCustomer;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Statamic;

class Customers extends Fieldtype
{
    public function preload()
    {
        $userField = new Field('user', [
            'type' => 'users',
            'max_items' => 1,
        ]);

        return [
            'user' => $userField->meta(),
            'convertGuestToUserUrl' => cp_route('cargo.fieldtypes.convert-guest-customer'),
            'canCreateUsers' => Statamic::pro() && User::current()->can('create', \Statamic\Contracts\Auth\User::class),
        ];
    }

    public function preProcess($data)
    {
        if (! is_object($data)) {
            return [
                'id' => $data,
                'invalid' => true,
            ];
        }

        if ($data instanceof GuestCustomer) {
            return [
                'type' => 'guest',
                'id' => $data->id(),
                'reference' => $data->id(),
                'name' => $data->name(),
                'email' => $data->email(),
                'viewable' => true,
                'editable' => false,
            ];
        }

        return [
            'type' => 'user',
            'id' => $data->id(),
            'reference' => $data->reference(),
            'name' => $data->name(),
            'email' => $data->email(),
            'viewable' => User::current()->can('view', $data),
            'editable' => User::current()->can('edit', $data),
            'edit_url' => $data->editUrl(),
        ];
    }

    public function preProcessIndex($data)
    {
        return $this->preProcess($data);
    }

    public function augment($value)
    {
        if (! $value) {
            return;
        }

        return $value;
    }
}
