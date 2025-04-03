<template>
    <div>
        <p v-if="!value.has_payment_gateway" class="text-sm">{{ __('No payment was required for this order.') }}</p>

        <div v-else class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex items-center">
                        <SvgIcon v-if="value.logo" :name="value.logo" class="mr-3 h-10 w-10" />

                        <div
                            class="text-md font-semibold"
                            :class="{ 'text-red-500 dark:text-red-950': value.invalid }"
                            v-tooltip.top="value.invalid ? __('This payment gateway is no longer installed') : null"
                        >
                            {{ value.title }}
                        </div>
                    </div>

                    <div v-if="value.details" :class="{ 'mt-3': !value.logo, 'mt-5': value.logo }">
                        <ul class="list-none space-y-2 text-xs">
                            <li v-for="(value, label) in value.details" :key="label">
                                <span class="mr-1 font-semibold">{{ label }}:</span>
                                <span v-html="value"></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SvgIcon from '../SvgIcon.vue';
import { Fieldtype } from 'statamic';

export default {
    components: { SvgIcon },

    mixins: [Fieldtype],
};
</script>
