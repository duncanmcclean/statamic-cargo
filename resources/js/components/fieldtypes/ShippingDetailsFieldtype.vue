<script setup>
import { Fieldtype } from '@statamic/cms';
import { Heading, Description, Icon } from '@statamic/cms/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <div>
        <Description v-if="!value.has_shipping_option">
            {{ __('No shipping option was selected for this order.') }}
        </Description>

        <div v-else>
            <div class="flex items-center">
                <Icon v-if="value.shipping_method.logo" :name="value.shipping_method.logo" class="mr-4 h-8 w-8" />

                <div class="flex flex-col space-y-1">
                    <Heading
                        :class="{ 'text-red-500 dark:text-red-950': value.invalid }"
                        v-tooltip.top="value.invalid ? __('This shipping method is no longer installed.') : null"
                        :text="value.name"
                    />
                    <Description
                        :class="{ 'text-red-500 dark:text-red-950': value.invalid }"
                        v-tooltip.top="value.invalid ? __('This shipping method is no longer installed.') : null"
                        :text="value.shipping_method.name"
                    />
                </div>
            </div>

            <hr v-if="value.details.length" class="my-4 border-gray-300 dark:border-gray-700" />

            <div v-if="value.details.length">
                <ul class="list-none space-y-2">
                    <li v-for="(value, label) in value.details" :key="label">
                        <Description>
                            <strong>{{ label }}:</strong> <span v-html="value" />
                        </Description>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>