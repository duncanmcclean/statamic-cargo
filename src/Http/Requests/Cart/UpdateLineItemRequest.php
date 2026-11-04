<?php

namespace DuncanMcClean\Cargo\Http\Requests\Cart;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Http\Requests\CustomizableFormRequest;
use Illuminate\Support\Traits\Localizable;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;

class UpdateLineItemRequest extends CustomizableFormRequest
{
    use Localizable;

    protected string $form = 'cart:update_line_item';

    public function authorize(): bool
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        return parent::authorize();
    }

    public function rules(): array
    {
        return [
            'variant' => ['nullable'],
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
