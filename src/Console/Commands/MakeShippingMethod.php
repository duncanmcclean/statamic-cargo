<?php

namespace DuncanMcClean\Cargo\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeShippingMethod extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:shipping-method';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new shipping method';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ShippingMethod';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'shipping-method.php.stub';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }
    }
}
