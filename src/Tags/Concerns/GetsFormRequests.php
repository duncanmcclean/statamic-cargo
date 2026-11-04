<?php

namespace DuncanMcClean\Cargo\Tags\Concerns;

use Statamic\Support\Str;

trait GetsFormRequests
{
    /**
     * Get the form request.
     *
     * @return string
     */
    protected function getFormRequest()
    {
        return $this->params->get('request');
    }

    /**
     * Parse form request class.
     *
     * @param  string  $formRequest
     * @param  string  $form
     * @return string
     */
    protected function parseFormRequest($formRequest, $form)
    {
        if (! Str::contains($formRequest, 'App\\')) {
            $formRequest = 'App\\Http\\Requests\\'.$formRequest;
        }

        if (! class_exists($formRequest)) {
            throw new \Exception("Form Request [{$formRequest}] does not exist.");
        }

        return encrypt(['class' => $formRequest, 'form' => $form]);
    }
}
