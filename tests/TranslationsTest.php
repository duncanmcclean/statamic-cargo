<?php

namespace Tests;

use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;

class TranslationsTest extends TestCase
{
    #[Test]
    public function french_translations_are_loaded_for_php_and_the_control_panel()
    {
        app()->setLocale('fr');

        $this->assertSame('Créer une réduction', __('Create Discount'));
        $this->assertSame('Commande n° 123', __('Order #:number', ['number' => 123]));
        $this->assertSame('Le code de réduction est invalide.', __('cargo::validation.invalid_discount_code'));

        $translations = app('translator')->toJson();

        $this->assertSame('Créer une réduction', $translations['*.Create Discount']);
        $this->assertSame('La commande a été créée', $translations['cargo::messages.timeline_events.order_created']);
    }

    #[Test]
    public function line_item_counts_are_pluralized_in_french()
    {
        app()->setLocale('fr');

        $key = '{1} :count line item|[2,*] :count line items';

        $this->assertSame('1 ligne de commande', trans_choice($key, 1));
        $this->assertSame('2 lignes de commande', trans_choice($key, 2));
    }

    #[Test]
    public function checkout_confirmation_translates_the_order_number_and_preserves_markup()
    {
        app()->setLocale('fr');

        $template = file_get_contents(__DIR__.'/../resources/views/checkout/confirmation.antlers.html');
        preg_match('/<p class="mb-4">(.*?)<\/p>/', $template, $matches);
        $output = (string) Antlers::parse($matches[1], ['order_number' => '123']);

        $this->assertSame(
            'Votre numéro de commande est <strong>#123</strong>. Nous vous enverrons un e-mail de confirmation dès que votre paiement aura été traité.',
            $output
        );
    }

    #[Test]
    public function email_templates_render_in_french()
    {
        app()->setLocale('fr');
        View::addNamespace('mail', app(Markdown::class)->htmlComponentPaths());

        $order = Order::make()->orderNumber('123')->customer([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
        ]);

        foreach (['order-confirmation' => 'confirmée', 'order-shipped' => 'expédiée'] as $template => $status) {
            $output = Blade::render(
                file_get_contents(__DIR__."/../src/Commands/stubs/install/{$template}.blade.php.stub"),
                ['order' => $order]
            );

            $this->assertStringContainsString("La commande n° 123 a été {$status}", $output);
            $this->assertStringContainsString('Merci pour votre commande !', $output);
            $this->assertStringNotContainsString('Thank you for your order!', $output);
        }
    }

    #[Test]
    public function english_strings_remain_available_as_fallbacks()
    {
        app()->setLocale('en');

        $this->assertSame('Create Discount', __('Create Discount'));
        $this->assertSame('Order #123 has been confirmed', __('Order #:orderNumber has been confirmed', ['orderNumber' => 123]));
    }
}
