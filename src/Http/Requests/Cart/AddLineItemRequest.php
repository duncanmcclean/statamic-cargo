<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Http\Requests\Concerns\AcceptsCustomFormRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddLineItemRequest extends FormRequest
{
    use AcceptsCustomFormRequests;

    public function authorize()
    {
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
            'product' => [
                'required',
                function ($attribute, $value, $fail) {
                    $product = Product::find($value);

                    if (! $product) {
                        return $fail(__('cargo::validation.product_not_found'));
                    }

                    if (! in_array($product->collectionHandle(), config('statamic.cargo.products.collections'))) {
                        $fail(__('cargo::validation.product_not_found'));
                    }
                },
            ],
            'variant' => [
                Rule::requiredIf(fn () => Product::find($this->product)?->isVariantProduct()),
                function ($attribute, $value, $fail) {
                    $product = Product::find($this->product);

                    if ($product->isVariantProduct()) {
                        $variant = $product->variant($value);

                        if (! $variant) {
                            return $fail(__('cargo::validation.variant_not_found'));
                        }
                    }

                },
            ],
            'quantity' => ['nullable', 'integer', 'gt:0'],
        ];
    }
}
