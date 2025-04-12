<?php

namespace DuncanMcClean\Cargo\Tags;

use DuncanMcClean\Cargo\Facades\Cart as CartFacade;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Support\Str;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Tags\Tags;

class Cart extends Tags
{
    use Concerns\FormBuilder;

    public function index()
    {
        if (! CartFacade::hasCurrentCart()) {
            return [];
        }

        return CartFacade::current()->toAugmentedArray();
    }

    public function wildcard($tag)
    {
        // To prevent empty carts being created unintentionally, we'll return default values for some fields.
        if (! CartFacade::hasCurrentCart()) {
            if (in_array($tag, ['grand_total', 'sub_total', 'discount_total', 'tax_total', 'shipping_total'])) {
                return Money::format(0, Site::current());
            }

            return null;
        }

        $cart = Blink::once('cart', fn () => CartFacade::current());

        if (method_exists($this, $method = Str::camel($tag))) {
            return $this->{$method}();
        }

        // When doing something like `{{ cart:customer:email }}`, we need to get customer first,
        // then we can use Laravel's Arr helper to get the value from the array.
        if (Str::contains($tag, ':')) {
            $field = Str::before($tag, ':');
            $key = Str::of($tag)->after(':')->replace(':', '.')->__toString();

            $array = $cart->augmentedValue($field);

            if ($array instanceof Value) {
                $array = $array->value();
            }

            return Arr::get($array, $key);
        }

        return $cart->augmentedValue($tag);
    }

    public function exists(): bool
    {
        return CartFacade::hasCurrentCart();
    }

    public function isEmpty(): bool
    {
        return ! CartFacade::hasCurrentCart() || CartFacade::current()->lineItems()->isEmpty();
    }

    public function added(): bool
    {
        if (! CartFacade::hasCurrentCart()) {
            return false;
        }

        return CartFacade::current()->lineItems()
            ->filter(fn (LineItem $lineItem) => $lineItem->product()->id() === $this->params->get('product'))
            ->when(
                $this->params->get('variant'),
                fn ($collection) => $collection->filter(fn (LineItem $lineItem) => $lineItem->variant()->key() === $this->params->get('variant'))
            )
            ->count() >= 1;
    }

    public function add()
    {
        if (! Str::contains($this->content, ['name="product"', 'name=\'product\'']) && $this->context->has('id')) {
            $this->content .= '<input type="hidden" name="product" value="'.$this->context->get('id').'">';
        }

        return $this->createForm(route('statamic.cargo.cart.line-items.store'), knownParams: ['product']);
    }

    public function updateLineItem()
    {
        if (! $this->params->has('line_item') && ! $this->params->has('product')) {
            throw new \Exception('You must provide a `line_item` or `product` parameter to the cart:update_line_item tag.');
        }

        $lineItem = CartFacade::current()->lineItems()
            ->when($this->params->get('line_item'), function ($collection, $lineItem) {
                return $collection->where('id', $lineItem);
            })
            ->when($this->params->get('product'), function ($collection, $product) {
                return $collection->where('product', $product);
            })
            ->when($this->params->get('variant'), function ($collection, $variant) {
                return $collection->where('variant', $variant);
            })
            ->first();

        return $this->createForm(
            action: route('statamic.cargo.cart.line-items.update', $lineItem->id()),
            data: $lineItem->toAugmentedArray(),
            method: 'PATCH'
        );
    }

    public function remove()
    {
        if (! $this->params->has('line_item') && ! $this->params->has('product')) {
            throw new \Exception('You must provide a `line_item` or `product` parameter to the cart:remove tag.');
        }

        $lineItem = CartFacade::current()->lineItems()
            ->when($this->params->get('line_item'), function ($collection, $lineItem) {
                return $collection->where('id', $lineItem);
            })
            ->when($this->params->get('product'), function ($collection, $product) {
                return $collection->where('product', $product);
            })
            ->when($this->params->get('variant'), function ($collection, $variant) {
                return $collection->where('variant', $variant);
            })
            ->first();

        return $this->createForm(
            action: route('statamic.cargo.cart.line-items.destroy', $lineItem->id()),
            data: $lineItem->toAugmentedArray(),
            method: 'DELETE'
        );
    }

    public function update()
    {
        $cart = CartFacade::current();

        return $this->createForm(
            action: route('statamic.cargo.cart.update'),
            data: $cart->toAugmentedArray(),
            method: 'PATCH',
        );
    }

    public function delete()
    {
        return $this->createForm(
            action: route('statamic.cargo.cart.destroy'),
            method: 'DELETE'
        );
    }
}
