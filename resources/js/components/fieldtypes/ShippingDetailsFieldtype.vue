<template>
    <div>
        <p v-if="!value.has_shipping_option" class="text-sm">
            {{ __('No shipping option was selected for this order.') }}
        </p>

        <div v-else class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex items-center">
                        <SvgIcon
                            v-if="value.shipping_method.logo"
                            :name="value.shipping_method.logo"
                            class="mr-3 h-10 w-10"
                        />

                        <div class="flex flex-col space-y-1">
                            <span class="text-md font-semibold">{{ value.name }}</span>
                            <span
                                class="text-xs"
                                :class="{ 'text-red-500 dark:text-red-950': value.invalid }"
                                v-tooltip.top="value.invalid ? __('This shipping method is no longer installed') : null"
                                >{{ value.shipping_method.name }}</span
                            >
                        </div>
                    </div>

                    <div class="mt-5">
                        <ul class="list-none space-y-3 text-xs">
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
