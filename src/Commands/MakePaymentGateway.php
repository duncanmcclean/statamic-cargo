<?php

namespace DuncanMcClean\Cargo\Commands;

use Statamic\Console\RunsInPlease;

class MakePaymentGateway extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:payment-gateway';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new payment gateway';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'PaymentGateway';

    /**
     * The stub to be used for generating the class.
     *
     * @var string
     */
    protected $stub = 'payment-gateway.php.stub';

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
