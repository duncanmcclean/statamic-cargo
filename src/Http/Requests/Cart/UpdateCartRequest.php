<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Http\Requests\Concerns\AcceptsCustomFormRequests;
use DuncanMcClean\Cargo\Rules\ValidDiscountCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;

class UpdateCartRequest extends FormRequest
{
    use AcceptsCustomFormRequests, Localizable;

    private $cachedFields;

    public function authorize()
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->authorize();
        }

        return true;
    }

    /**
     * Optionally override the redirect url based on the presence of _error_redirect
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        if ($redirect = $this->input('_error_redirect')) {
            return $url->to($redirect);
        }

        return $url->previous();
    }

    public function rules()
    {
        $fields = $this->getFormFields();

        return $fields
            ->validator()
            ->withRules($this->extraRules())
            ->validator()
            ->getRules();
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->ajax()) {
            $errors = $validator->errors();

            $response = response([
                'errors' => $errors->all(),
                'error' => collect($errors->messages())->map(function ($errors, $field) {
                    return $errors[0];
                })->all(),
            ], 400);

            throw (new ValidationException($validator, $response));
        }

        return parent::failedValidation($validator);
    }

    private function extraRules()
    {
        return [
            'customer' => ['nullable', 'array'],
            'customer.name' => ['nullable', 'string'],
            'customer.first_name' => ['nullable', 'string'],
            'customer.last_name' => ['nullable', 'string'],
            'customer.email' => ['nullable', 'email'],
            'name' => ['nullable', 'string'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'discount_code' => ['nullable', 'string', new ValidDiscountCode],
            'shipping_method' => ['nullable', 'string'],
            'shipping_option' => ['nullable', 'string'],
        ];
    }

    private function getFormFields()
    {
        if ($this->cachedFields) {
            return $this->cachedFields;
        }

        $fields = Order::blueprint()->fields();
        $fields->only(Cart::current()->updatableFields());

        return $this->cachedFields = $fields->addValues($this->all());
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
