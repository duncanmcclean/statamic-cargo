<?php

namespace DuncanMcClean\Cargo\Tags\Concerns;

use Statamic\Tags\Concerns\GetsRedirects;
use Statamic\Tags\Concerns\RendersForms;

trait FormBuilder
{
    use GetsFormRequests, GetsRedirects, RendersForms;

    protected function createForm(
        string $action,
        array $data = [],
        string $method = 'POST',
        array $knownParams = [],
    ): string|array {
        $attrs = [];
        $params = [];

        $knownParams = array_merge($knownParams, [
            'request', 'redirect', 'error_redirect', 'line_item', 'product', 'variant',
        ]);

        if ($errors = $this->errors()) {
            $data['errors'] = $errors;
        }

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if ($formRequest = $this->getFormRequest()) {
            $params['request'] = $this->parseFormRequest($formRequest);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams, $attrs),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams, $attrs);
        $html .= $this->formMetaFields($params);
        $html .= $this->parse($data);
        $html .= $this->formClose();

        return $html;
    }

    public function errors()
    {
        $errors = session()->get("{$this->sessionHandle()}.errors", []);

        // If this is a single tag just output a boolean.
        if ($this->content === '') {
            return ! empty($errors);
        }

        return $this->parseLoop(collect($errors)->map(function ($error) {
            return ['value' => $error];
        }));
    }

    protected function sessionHandle(): string
    {
        return 'cargo.'.$this->handle();
    }
}
