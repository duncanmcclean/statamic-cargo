<?php

namespace DuncanMcClean\Cargo\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class CustomizableFormRequest extends FormRequest
{
    private ?FormRequest $customFormRequestInstance = null;

    public function authorize(): bool
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->authorize();
        }

        return true;
    }

    public function rules(): array
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->rules();
        }

        return [];
    }

    public function messages(): array
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->messages();
        }

        return [];
    }

    public function attributes(): array
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->attributes();
        }

        return [];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->hasCustomFormRequest()) {
            return;
        }

        $customRequest = $this->resolveCustomFormRequest();

        if (method_exists($customRequest, 'prepareForValidation')) {
            $customRequest->prepareForValidation();
            $this->merge($customRequest->all());
        }
    }

    public function withValidator(Validator $validator): void
    {
        if (! $this->hasCustomFormRequest()) {
            return;
        }

        $customRequest = $this->resolveCustomFormRequest();

        if (method_exists($customRequest, 'withValidator')) {
            $customRequest->withValidator($validator);
        }
    }

    protected function passedValidation(): void
    {
        if (! $this->hasCustomFormRequest()) {
            return;
        }

        $customRequest = $this->resolveCustomFormRequest();

        if (method_exists($customRequest, 'passedValidation')) {
            $customRequest->passedValidation();
        }
    }

    protected function hasCustomFormRequest(): bool
    {
        return $this->has('_request');
    }

    protected function resolveCustomFormRequest(): FormRequest
    {
        if ($this->customFormRequestInstance) {
            return $this->customFormRequestInstance;
        }

        $formRequest = decrypt($this->input('_request'));

        if (! class_exists($formRequest)) {
            throw new \Exception("Form Request [{$formRequest}] does not exist.");
        }

        return $this->customFormRequestInstance = app($formRequest, [
            $this->query(),
            $this->all(),
            $this->attributes->all(),
            $this->cookies->all(),
            $this->files->all(),
            $this->server->all(),
            $this->content,
        ]);
    }
}
