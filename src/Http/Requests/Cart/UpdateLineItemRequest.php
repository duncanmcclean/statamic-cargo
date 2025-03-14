<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Exceptions\NotFoundHttpException;

class UpdateLineItemRequest extends FormRequest
{
    public function authorize()
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        return true;
    }

    public function rules()
    {
        return [
            'variant' => ['nullable'],
            'quantity' => ['nullable', 'integer', 'gt:0'],
        ];
    }
}
