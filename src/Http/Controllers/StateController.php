<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Data\States;
use Illuminate\Http\Request;

class StateController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'country' => ['required', 'string'],
        ]);

        return States::byCountry($validated['country']);
    }
}
