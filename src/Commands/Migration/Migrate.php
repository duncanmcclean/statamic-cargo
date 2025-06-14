<?php

namespace DuncanMcClean\Cargo\Commands\Migration;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class Migrate extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate';

    protected $description = 'Migrates everything from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $this->output->write(PHP_EOL.'<fg=#02747E;options=bold>
     ______                     
    / ____/___ __________ _____ 
   / /   / __ `/ ___/ __ `/ __ \
  / /___/ /_/ / /  / /_/ / /_/ /
  \____/\__,_/_/   \__, /\____/ 
                  /____/        
                </>'.PHP_EOL);

        if (! $this->input->isInteractive()) {
            $this->components->warn('Please run this command interactively.');

            return;
        }

        $this->call('statamic:cargo:migrate:configs');
        $this->newLine();

        $this->call('statamic:cargo:migrate:customers');
        $this->newLine();

        $this->call('statamic:cargo:migrate:discounts');
        $this->newLine();

        $this->call('statamic:cargo:migrate:taxes');
        $this->newLine();

        $this->call('statamic:cargo:migrate:orders');
        $this->newLine();

        $this->call('statamic:cargo:migrate:carts');
        $this->newLine();

        $this->call('statamic:cargo:migrate:products');
        $this->newLine();
    }
}
