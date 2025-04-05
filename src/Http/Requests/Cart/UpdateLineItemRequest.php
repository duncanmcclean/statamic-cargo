<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Http\Requests\Concerns\AcceptsCustomFormRequests;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Exceptions\NotFoundHttpException;

class UpdateLineItemRequest extends FormRequest
{
    use AcceptsCustomFormRequests;

    public function authorize()
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->authorize();
        }

        return true;
    }

    public function rules()
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->rules();
        }

        return [
            'variant' => ['nullable'],
            'quantity' => ['nullable', 'integer', 'gt:0'],
        ];
    }
}
