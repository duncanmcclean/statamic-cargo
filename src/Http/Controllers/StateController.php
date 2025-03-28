<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Fieldtypes\States;
use Illuminate\Http\Request;

class StateController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'country' => ['required', 'string'],
        ]);

        return (new States)->getStates($validated['country']);
    }
}
