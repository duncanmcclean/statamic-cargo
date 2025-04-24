<?php

namespace DuncanMcClean\Cargo\Console\Commands\Migration\Concerns;

use DuncanMcClean\Cargo\Data\States;
use Illuminate\Support\Collection;
use Statamic\Facades\Dictionary;
use Statamic\Support\Str;

trait MapsAddresses
{
    private function mapAddresses(Collection $data): array
    {
        $useShippingAddressForBilling = $data->get('use_shipping_address_for_billing') === true
            || $data->get('use_shipping_address_for_billing') == 1;

        $shippingCountry = $shippingState = $billingCountry = $billingState = null;

        if ($data->get('shipping_country')) {
            [$shippingCountry, $shippingState] = $this->mapCountryAndState(
                $data->get('shipping_country'),
                $data->get('shipping_region')
            );
        }

        if ($data->get('billing_country')) {
            [$billingCountry, $billingState] = $this->mapCountryAndState(
                $data->get('billing_country'),
                $data->get('billing_region')
            );
        }

        return [
            'shipping_name' => $shippingName = $data->get('shipping_name'),
            'shipping_line_1' => $shippingLine1 = $data->get('shipping_address', $data->get('shipping_address_line1')),
            'shipping_line_2' => $shippingLine2 = $data->get('shipping_address_line2'),
            'shipping_city' => $shippingCity = $data->get('shipping_city'),
            'shipping_state' => $shippingState,
            'shipping_postcode' => $shippingPostcode = $data->get('shipping_zip_code', $data->get('shipping_postal_code')),
            'shipping_country' => $shippingCountry,
            'billing_name' => $useShippingAddressForBilling ? $shippingName : $data->get('billing_name'),
            'billing_line_1' => $useShippingAddressForBilling ? $shippingLine1 : $data->get('billing_address', $data->get('billing_address_line1')),
            'billing_line_2' => $useShippingAddressForBilling ? $shippingLine2 : $data->get('billing_address_line1'),
            'billing_city' => $useShippingAddressForBilling ? $shippingCity : $data->get('billing_city'),
            'billing_state' => $useShippingAddressForBilling ? $shippingState : $billingState,
            'billing_postcode' => $useShippingAddressForBilling ? $shippingPostcode : $data->get('billing_zip_code', $data->get('billing_postal_code')),
            'billing_country' => $useShippingAddressForBilling ? $shippingCountry : $billingCountry,
        ];
    }

    /**
     * Simple Commerce saved countries in ISO2, but Cargo uses ISO3. So, we need to convert
     * them, along with the state codes.
     */
    private function mapCountryAndState(string $countryIso2, ?string $stateCode = null): array
    {
        $countries = Dictionary::find('countries')->optionItems();

        $country = array_find($countries, function ($country) use ($countryIso2) {
            return $country->extra()['iso2'] === $countryIso2;
        });

        $states = States::byCountry($country['iso3']);

        $state = $states->filter(function (array $state) use ($stateCode) {
            $code = Str::after($stateCode, '-');

            return strtolower($state['code']) === strtolower($code);
        })->first();

        return [$country['iso3'] ?? $countryIso2, $state['code'] ?? $stateCode];
    }
}
