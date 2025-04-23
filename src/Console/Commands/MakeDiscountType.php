<?php

namespace DuncanMcClean\Cargo\Console\Commands;

use Statamic\Console\RunsInPlease;

class MakeDiscountType extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:discount-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new discount type';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'DiscountType';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'discount-type.php.stub';

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
