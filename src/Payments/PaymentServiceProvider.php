<?php

namespace DuncanMcClean\Cargo\Payments;

use DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway;
use Statamic\Providers\AddonServiceProvider;

class PaymentServiceProvider extends AddonServiceProvider
{
    protected array $paymentGateways = [
        Gateways\Dummy::class,
        Gateways\Mollie::class,
        Gateways\PayOnDelivery::class,
        Gateways\Stripe::class,
    ];

    public function bootAddon()
    {
        foreach ($this->paymentGateways as $paymentGateway) {
            $paymentGateway::register();
        }

        $this->registerAppExtensions('PaymentGateways', PaymentGateway::class);
    }

    protected function registerAppExtensions($folder, $requiredClass)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->allFiles($path) as $file) {
            $relativePathOfFolder = str_replace(app_path(DIRECTORY_SEPARATOR), '', $file->getPath());
            $namespace = str_replace('/', '\\', $relativePathOfFolder);
            $class = $file->getBasename('.php');

            $fqcn = $this->app->getNamespace()."{$namespace}\\{$class}";
            if (is_subclass_of($fqcn, $requiredClass)) {
                $fqcn::register();
            }
        }
    }
}
