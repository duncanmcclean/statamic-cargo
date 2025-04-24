<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Product;
use DuncanMcClean\Cargo\Http\Requests\Concerns\AcceptsCustomFormRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\Rule;
use Statamic\Facades\Site;

class AddLineItemRequest extends FormRequest
{
    use AcceptsCustomFormRequests, Localizable;

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

    public function validateResolved()
    {
        // If this was submitted from a front-end form, we want to use the appropriate language
        // for the translation messages. If there's no previous url, it was likely submitted
        // directly in a headless format. In that case, we'll just use the default lang.
        $site = ($previousUrl = $this->previousUrl()) ? Site::findByUrl($previousUrl) : null;

        return $this->withLocale($site?->lang(), fn () => parent::validateResolved());
    }

    private function previousUrl()
    {
        return ($referrer = request()->header('referer'))
            ? url()->to($referrer)
            : session()->previousUrl();
    }
}
