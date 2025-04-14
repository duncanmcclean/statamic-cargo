<?php

namespace DuncanMcClean\Cargo\Discounts;

use DuncanMcClean\Cargo\Discounts\Types\DiscountType;
use Statamic\Providers\AddonServiceProvider;

class DiscountServiceProvider extends AddonServiceProvider
{
    protected array $discountTypes = [
        Types\AmountOff::class,
        Types\PercentageOff::class,
    ];

    public function bootAddon()
    {
        foreach ($this->discountTypes as $discountType) {
            $discountType::register();
        }

        $this->registerAppExtensions('DiscountTypes', DiscountType::class);
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
