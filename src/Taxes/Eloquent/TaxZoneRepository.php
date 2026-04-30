<?php

namespace DuncanMcClean\Cargo\Taxes\Eloquent;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone;
use DuncanMcClean\Cargo\Taxes\File\TaxZoneRepository as FileRepository;
use Illuminate\Support\Collection;

class TaxZoneRepository extends FileRepository
{
    public function all(): Collection
    {
        return TaxZoneModel::query()->get()->mapWithKeys(function (TaxZoneModel $model) {
            return [$model->handle => $this->make()->handle($model->handle)->data($model->data ?? [])];
        });
    }

    public function find(string $handle): ?TaxZone
    {
        $model = TaxZoneModel::query()->find($handle);

        if (! $model) {
            return null;
        }

        return $this->make()->handle($model->handle)->data($model->data ?? []);
    }

    public function save(TaxZone $taxZone): void
    {
        TaxZoneModel::query()->updateOrCreate(
            ['handle' => $taxZone->handle()],
            ['data' => $taxZone->fileData()]
        );
    }

    public function delete(string $handle): void
    {
        TaxZoneModel::query()->where('handle', $handle)->delete();
    }
}
