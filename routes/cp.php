<?php

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Http\Controllers\CP\Discounts\DiscountActionController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Discounts\DiscountController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Fieldtypes\ConvertGuestCustomerController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Fieldtypes\StateFieldtypeController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Orders\OrderActionController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Orders\OrderController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Orders\PackingSlipController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Taxes\TaxClassController;
use DuncanMcClean\Cargo\Http\Controllers\CP\Taxes\TaxZoneController;
use Illuminate\Support\Facades\Route;

Route::name('cargo.')->group(function () {
    Route::resource('discounts', DiscountController::class)->except(['show', 'destroy']);
    Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);

    if (Cargo::usingDefaultTaxDriver()) {
        Route::resource('tax-classes', TaxClassController::class)->except('show');
        Route::resource('tax-zones', TaxZoneController::class)->except('show');
    }

    Route::prefix('discounts')->name('discounts.')->group(function () {
        Route::post('actions', [DiscountActionController::class, 'run'])->name('actions.run');
        Route::post('actions/list', [DiscountActionController::class, 'bulkActions'])->name('actions.bulk');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::post('actions', [OrderActionController::class, 'run'])->name('actions.run');
        Route::post('actions/list', [OrderActionController::class, 'bulkActions'])->name('actions.bulk');

        Route::get('{order}/packing-slip', PackingSlipController::class)->name('packing-slip');
    });

    Route::prefix('fieldtypes')->name('fieldtypes.')->group(function () {
        Route::get('states', StateFieldtypeController::class)->name('states');
        Route::post('convert-guest-customer', ConvertGuestCustomerController::class)->name('convert-guest-customer');
    });
});
