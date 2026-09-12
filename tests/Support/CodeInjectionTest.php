<?php

namespace Tests\Support;

use DuncanMcClean\Cargo\Support\CodeInjection;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CodeInjectionTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = __DIR__.'/__fixtures__/AppServiceProvider.php';

        File::ensureDirectoryExists(dirname($this->file));
        File::put($this->file, <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }
}
PHP);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->file));

        parent::tearDown();
    }

    #[Test]
    public function it_injects_imports()
    {
        CodeInjection::injectImports($this->file, [
            'Illuminate\Support\Facades\Mail',
            'App\Mail\OrderConfirmation',
        ]);

        $this->assertStringContainsString(<<<'PHP'
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
PHP, File::get($this->file));
    }

    #[Test]
    public function it_injects_aliased_imports()
    {
        CodeInjection::injectImports($this->file, [
            'App\Mail\OrderShipped',
            'DuncanMcClean\Cargo\Events\OrderShipped' => 'OrderShippedEvent',
        ]);

        $this->assertStringContainsString(<<<'PHP'
use App\Mail\OrderShipped;
use DuncanMcClean\Cargo\Events\OrderShipped as OrderShippedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
PHP, File::get($this->file));
    }

    #[Test]
    public function it_does_not_duplicate_existing_imports()
    {
        CodeInjection::injectImports($this->file, [
            'Illuminate\Support\Facades\Event',
            'Illuminate\Support\ServiceProvider',
        ]);

        $contents = File::get($this->file);

        $this->assertEquals(1, substr_count($contents, 'use Illuminate\Support\Facades\Event;'));
        $this->assertEquals(1, substr_count($contents, 'use Illuminate\Support\ServiceProvider;'));
    }
}
