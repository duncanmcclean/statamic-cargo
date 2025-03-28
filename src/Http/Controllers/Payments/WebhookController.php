<?php

namespace DuncanMcClean\Cargo\Http\Controllers\Payments;

use DuncanMcClean\Cargo\Facades\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Exceptions\NotFoundHttpException;

class WebhookController
{
    public function __invoke(Request $request, string $paymentGateway)
    {
        $paymentGateway = PaymentGateway::find($paymentGateway);

        throw_if(! $paymentGateway, NotFoundHttpException::class);

        return $paymentGateway->webhook($request);
    }
}
