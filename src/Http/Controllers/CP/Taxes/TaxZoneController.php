<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Taxes;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class TaxZoneController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('manage taxes');

        $taxZones = TaxZone::all()->map(function ($taxZone) {
            return [
                'id' => $taxZone->handle(),
                'handle' => $taxZone->handle(),
                'name' => $taxZone->get('name'),
                'type' => match ($taxZone->get('type')) {
                    'everywhere' => __('Everywhere'),
                    'countries' => __('Countries (:count)', ['count' => count($taxZone->get('countries', []))]),
                    'states' => __('States (:count)', ['count' => count($taxZone->get('states', []))]),
                    'postcodes' => __('Postcodes (:count)', ['count' => count($taxZone->get('postcodes', []))]),
                },
                'edit_url' => $taxZone->editUrl(),
                'delete_url' => $taxZone->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $taxZones;
        }

        return view('cargo::cp.tax-zones.index', [
            'taxZones' => $taxZones,
            'columns' => [
                Column::make('name')->label(__('Name')),
                Column::make('type')->label(__('Type')),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxZone::blueprint())
            ->icon(Cargo::svg('tax-zones'))
            ->title(__('Create Tax Zone'))
            ->submittingTo(cp_route('cargo.tax-zones.store'), 'POST');
    }

    public function store(Request $request)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxZone::blueprint())->submit($request->values);

        $taxZone = TaxZone::make()
            ->handle(Str::slug(Arr::get($values, 'name')))
            ->data($values);

        $taxZone->save();

        return ['redirect' => $taxZone->editUrl()];
    }

    public function edit(Request $request, $taxZone)
    {
        $this->authorize('manage taxes');

        return PublishForm::make(TaxZone::blueprint())
            ->icon(Cargo::svg('tax-zones'))
            ->title($taxZone->get('name'))
            ->values($taxZone->data()->all())
            ->submittingTo($taxZone->updateUrl());
    }

    public function update(Request $request, $taxZone)
    {
        $this->authorize('manage taxes');

        $values = PublishForm::make(TaxZone::blueprint())->submit($request->values);

        $taxZone->data($values)->save();
    }

    public function destroy(Request $request, $taxZone)
    {
        $this->authorize('manage taxes');

        $taxZone->delete();
    }
}
