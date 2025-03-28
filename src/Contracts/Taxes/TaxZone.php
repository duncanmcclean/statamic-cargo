<?php

namespace DuncanMcClean\Cargo\Contracts\Taxes;

interface TaxZone
{
    public function handle($handle = null);

    public function rates(): \Illuminate\Support\Collection;

    public function save(): bool;

    public function delete(): bool;

    public function editUrl(): string;

    public function updateUrl(): string;

    public function deleteUrl(): string;

    public function toArray(): array;

    public function fileData(): array;

    public function augmentedArrayData(): array;
}
