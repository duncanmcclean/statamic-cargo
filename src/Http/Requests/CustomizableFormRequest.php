<?php

namespace DuncanMcClean\Cargo\Http\Requests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

abstract class CustomizableFormRequest extends FormRequest
{
    protected string $form;

    private ?FormRequest $customFormRequest = null;

    private ?string $customFormRequestError = null;

    public function authorize(): bool
    {
        return $this->customFormRequest()?->authorize() ?? true;
    }

    protected function validationRules(): array
    {
        $rules = parent::validationRules();

        foreach ($this->customFormRequest()?->rules() ?? [] as $attribute => $customRules) {
            $rules[$attribute] = array_merge(
                $this->explodeRules($rules[$attribute] ?? []),
                $this->explodeRules($customRules)
            );
        }

        return $rules;
    }

    private function explodeRules(mixed $rules): array
    {
        return is_string($rules) ? explode('|', $rules) : Arr::wrap($rules);
    }

    public function messages(): array
    {
        return $this->customFormRequest()?->messages() ?? [];
    }

    public function attributes(): array
    {
        return $this->customFormRequest()?->attributes() ?? [];
    }

    protected function prepareForValidation(): void
    {
        $customFormRequest = $this->customFormRequest();

        if ($customFormRequest && method_exists($customFormRequest, 'prepareForValidation')) {
            $customFormRequest->prepareForValidation();
            $this->merge($customFormRequest->all());
        }
    }

    public function withValidator(Validator $validator): void
    {
        if ($this->customFormRequestError) {
            $validator->after(fn (Validator $validator) => $validator->errors()->add('_request', $this->customFormRequestError));
        }

        $customFormRequest = $this->customFormRequest();

        if ($customFormRequest && method_exists($customFormRequest, 'withValidator')) {
            $customFormRequest->withValidator($validator);
        }
    }

    protected function passedValidation(): void
    {
        $customFormRequest = $this->customFormRequest();

        if ($customFormRequest && method_exists($customFormRequest, 'passedValidation')) {
            $customFormRequest->passedValidation();
        }
    }

    private function customFormRequest(): ?FormRequest
    {
        if ($this->customFormRequest || $this->customFormRequestError || ! $this->has('_request')) {
            return $this->customFormRequest;
        }

        $token = $this->decodeFormRequestToken();
        $class = Arr::get($token, 'class');

        if (! is_string($class) || ! is_subclass_of($class, FormRequest::class)) {
            $this->customFormRequestError = __('cargo::validation.form_request_invalid');

            return null;
        }

        if (Arr::get($token, 'form') !== $this->form) {
            $this->customFormRequestError = __('cargo::validation.form_request_mismatch', ['form' => $this->form]);

            return null;
        }

        return $this->customFormRequest = app($class, [
            $this->query(),
            $this->all(),
            $this->attributes->all(),
            $this->cookies->all(),
            $this->files->all(),
            $this->server->all(),
            $this->content,
        ]);
    }

    private function decodeFormRequestToken(): array
    {
        if (! is_string($this->input('_request'))) {
            return [];
        }

        try {
            $token = decrypt($this->input('_request'));
        } catch (DecryptException) {
            return [];
        }

        return is_array($token) ? $token : [];
    }
}
