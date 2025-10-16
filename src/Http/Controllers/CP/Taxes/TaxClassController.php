<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Taxes;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Facades\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
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
                'title' => $taxClass->get('title'),
                'edit_url' => $taxClass->editUrl(),
                'delete_url' => $taxClass->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $taxClasses;
        }

        if ($taxClasses->isEmpty()) {
            return Inertia::render('cargo::TaxClasses/Empty', [
                'icon' => Cargo::svg('tax-classes'),
                'createUrl' => cp_route('cargo.tax-classes.create'),
            ]);
        }

        return Inertia::render('cargo::TaxClasses/Index', [
            'taxClasses' => $taxClasses,
            'columns' => [
                Column::make('title')->label(__('Title')),
            ],
            'icon' => Cargo::svg('tax-classes'),
            'createUrl' => cp_route('cargo.tax-classes.create'),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxClass::blueprint())
            ->asConfig()
            ->icon(Cargo::svg('tax-classes'))
            ->title(__('Create Tax Class'))
            ->submittingTo(cp_route('cargo.tax-classes.store'), 'POST');
    }

    public function store(Request $request)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxClass::blueprint())->submit($request->all());

        $taxClass = TaxClass::make()
            ->handle(Str::slug(Arr::get($values, 'title')))
            ->data($values);

        $taxClass->save();

        return ['redirect' => $taxClass->editUrl()];
    }

    public function edit(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxClass::blueprint())
            ->asConfig()
            ->icon(Cargo::svg('tax-classes'))
            ->title($taxClass->get('title'))
            ->values($taxClass->data()->all())
            ->submittingTo($taxClass->updateUrl());
    }

    public function update(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxClass::blueprint())->submit($request->all());

        $taxClass->data($values)->save();
    }

    public function destroy(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $taxClass->delete();
    }
}
