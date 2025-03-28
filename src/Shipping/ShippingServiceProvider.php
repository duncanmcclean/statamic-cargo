<?php

namespace DuncanMcClean\Cargo\Shipping;

use Statamic\Providers\AddonServiceProvider;

class ShippingServiceProvider extends AddonServiceProvider
{
    protected array $shippingMethods = [
        FreeShipping::class,
    ];

    public function bootAddon()
    {
        foreach ($this->shippingMethods as $shippingMethod) {
            $shippingMethod::register();
        }

        $this->registerAppExtensions('ShippingMethods', ShippingMethod::class);
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
