<?php

namespace DuncanMcClean\Cargo\Taxes\Eloquent;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use DuncanMcClean\Cargo\Taxes\File\TaxClassRepository as FileRepository;
use Illuminate\Support\Collection;

class TaxClassRepository extends FileRepository
{
    public function all(): Collection
    {
        return TaxClassModel::query()->get()->map(function (TaxClassModel $model) {
            return $this->make()->handle($model->handle)->data($model->data ?? []);
        });
    }

    public function find(string $handle): ?TaxClass
    {
        $model = TaxClassModel::query()->find($handle);

        if (! $model) {
            return null;
        }

        return $this->make()->handle($model->handle)->data($model->data ?? []);
    }

    public function save(TaxClass $taxClass): void
    {
        TaxClassModel::query()->updateOrCreate(
            ['handle' => $taxClass->handle()],
            ['data' => $taxClass->fileData()]
        );
    }

    public function delete(string $handle): void
    {
        TaxClassModel::query()->where('handle', $handle)->delete();
    }
}
