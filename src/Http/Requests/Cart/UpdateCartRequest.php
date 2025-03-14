<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateCartRequest extends FormRequest
{
    public $blueprintFields;
    public $submittedValues;

    public function authorize()
    {
        return Cart::hasCurrentCart();
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->isPrecognitive() || $this->wantsJson()) {
            return parent::failedValidation($validator);
        }

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

        $errorResponse = $this->has('_error_redirect') ? redirect($this->input('_error_redirect')) : back();

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors($validator->errors(), 'cart.update')));
    }

    public function processedValues()
    {
        return $this->blueprintFields->process()->values()
            ->only(Cart::current()->updatableFields())
            ->only(array_keys($this->submittedValues));
    }

    public function validator()
    {
        $cart = Cart::current();
        $fields = $cart->blueprint()->fields()->except(['customer', 'coupon']);

        $this->submittedValues = $this->only($cart->updatableFields());
        $this->blueprintFields = $fields->addValues($this->submittedValues);

        return $this->blueprintFields
            ->validator()
            ->withRules([
                'customer' => ['nullable', 'array'],
                'customer.name' => ['nullable', 'string'],
                'customer.first_name' => ['nullable', 'string'],
                'customer.last_name' => ['nullable', 'string'],
                'customer.email' => ['nullable', 'email'],
                'name' => ['nullable', 'string'],
                'first_name' => ['nullable', 'string'],
                'last_name' => ['nullable', 'string'],
                'email' => ['nullable', 'email'],
                'coupon' => ['nullable', 'string'],
                'shipping_method' => ['nullable', 'string'],
                'shipping_option' => ['nullable', 'string'],
            ])
            ->validator();
    }
}
