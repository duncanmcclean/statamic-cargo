<?php

namespace DuncanMcClean\Cargo\Http\Requests\Concerns;

use Illuminate\Foundation\Http\FormRequest;

trait AcceptsCustomFormRequests
{
    public function authorize(): bool
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->authorize();
        }

        return false;
    }

    public function rules()
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->rules();
        }

        return [];
    }

    public function messages()
    {
        if ($this->hasCustomFormRequest()) {
            return $this->resolveCustomFormRequest()->messages();
        }

        return [];
    }

    private function hasCustomFormRequest(): bool
    {
        return $this->has('_request');
    }

    private function resolveCustomFormRequest(): FormRequest
    {
        $formRequest = decrypt($this->input('_request'));

        if (! class_exists($formRequest)) {
            throw new \Exception("Form Request [{$formRequest}] does not exist.");
        }

        return app($formRequest, [
            $this->query(),
            $this->all(),
            $this->attributes(),
            $this->cookies->all(),
            $this->files->all(),
            $this->server(),
            $this->content,
        ]);
    }
}
