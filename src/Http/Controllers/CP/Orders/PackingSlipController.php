<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Orders;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\View\Cascade;

class PackingSlipController extends CpController
{
    public function __invoke(Request $request, $order)
    {
        $this->authorize('edit', $order);

        return view('cargo::packing-slip', [
            'config' => Cascade::config(),
            'order' => $order,
        ]);
    }
}
