<?php

namespace DuncanMcClean\Cargo\Fieldtypes;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Facades\TaxClass;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Relationship;

class TaxClasses extends Relationship
{
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $selectable = false;
    protected $formComponent = 'ui-publish-form';
    protected $formStackSize = 'narrow';

    protected $formComponentProps = [
        'icon' => 'icon',
        'title' => 'title',
        'blueprint' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'submitUrl' => 'submitUrl',
        'submitMethod' => 'submitMethod',
        'readOnly' => 'readOnly',
    ];

    public function icon()
    {
        return Cargo::svg('tax-classes');
    }

    protected function toItemArray($id)
    {
        $taxClass = TaxClass::find($id);

        if (! $taxClass) {
            return $this->invalidItemArray($id);
        }

        return [
            'id' => $taxClass->handle(),
            'title' => $taxClass->get('title'),
            'edit_url' => $taxClass->editUrl(),
        ];
    }

    public function getIndexItems($request)
    {
        return TaxClass::all()->map(function ($taxClass) {
            return [
                'id' => $taxClass->handle(),
                'title' => $taxClass->get('title'),
            ];
        });
    }

    protected function augmentValue($value)
    {
        return TaxClass::find($value);
    }

    public function preProcessIndex($data)
    {
        return $this->getItemsForPreProcessIndex($data)->map(function ($item) {
            return [
                'id' => $item->handle(),
                'title' => $item->get('title'),
                'edit_url' => $item->editUrl(),
            ];
        });
    }

    protected function getCreatables()
    {
        $user = User::current();

        if (! $user->can('manage taxes')) {
            return [];
        }

        return [['url' => cp_route('cargo.tax-classes.create')]];
    }

    protected function getCreateItemUrl()
    {
        return cp_route('cargo.tax-classes.create');
    }

    public function rules(): array
    {
        return [
            function ($attribute, $value, $fail) {
                if (! TaxClass::find($value[0])) {
                    $fail(__('cargo::validation.tax_class_invalid'));
                }
            },
        ];
    }
}
