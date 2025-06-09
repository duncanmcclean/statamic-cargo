<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Taxes;

use DuncanMcClean\Cargo\Facades\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Http\Controllers\CP\CpController;

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
                Column::make('handle')->label(__('Handle')),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('manage taxes');

        $blueprint = TaxClass::blueprint();

        $fields = $blueprint->fields()->preProcess();
        $values = $fields->values();

        $viewData = [
            'title' => __('Create Tax Class'),
            'actions' => [
                'save' => cp_route('cargo.tax-classes.store'),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('cargo::cp.tax-classes.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('manage taxes');

        $blueprint = TaxClass::blueprint();

        $data = $request->all();

        $fields = $blueprint->fields()->addValues($data);

        $fields->validator()->validate();

        $values = $fields->process()->values();

        $taxClass = TaxClass::make()
            ->handle(Str::slug($values->get('name')))
            ->data($values->except('handle'));

        $saved = $taxClass->save();

        return [
            'saved' => $saved,
            'redirect' => $taxClass->editUrl(),
        ];
    }

    public function edit(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxClass::blueprint())
            ->title(__('Edit Tax Class'))
            ->values($taxClass->data()->all())
            ->submittingTo($taxClass->updateUrl());
    }

    public function update(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxClass::blueprint())->submit($request->values);

        $taxClass->merge($values);

        $saved = $taxClass->save();

        return ['saved' => $saved];
    }

    public function destroy(Request $request, $taxClass)
    {
        $this->authorize('manage taxes');

        $taxClass->delete();

        return response('', 204);
    }
}
