<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Taxes;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Facades\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class TaxClassController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('manage taxes');

        $taxClasses = TaxClass::all()->map(function ($taxClass) {
            return [
                'id' => $taxClass->handle(),
                'handle' => $taxClass->handle(),
                'name' => $taxClass->get('name'),
                'edit_url' => $taxClass->editUrl(),
                'delete_url' => $taxClass->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $taxClasses;
        }

        return view('cargo::cp.tax-classes.index', [
            'taxClasses' => $taxClasses,
            'columns' => [
                Column::make('name')->label(__('Name')),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxClass::blueprint())
            ->icon(Cargo::svg('tax-classes'))
            ->title(__('Create Tax Class'))
            ->submittingTo(cp_route('cargo.tax-classes.store'), 'POST');
    }

    public function store(Request $request)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxClass::blueprint())->submit($request->values);

        $taxClass = TaxClass::make()
            ->handle(Str::slug(Arr::get($values, 'name')))
            ->data($values);

        $taxClass->save();

        return ['redirect' => $taxClass->editUrl()];
    }

    public function edit(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxClass::blueprint())
            ->icon(Cargo::svg('tax-classes'))
            ->title(__('Edit Tax Class'))
            ->values($taxClass->data()->all())
            ->submittingTo($taxClass->updateUrl());
    }

    public function update(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxClass::blueprint())->submit($request->values);

        $taxClass->data($values)->save();
    }

    public function destroy(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $taxClass->delete();
    }
}
