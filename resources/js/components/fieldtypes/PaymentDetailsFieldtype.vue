<script setup>
import { Fieldtype } from '@statamic/cms';
import { Heading, Description, Icon } from '@statamic/cms/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const hasDetails = computed(() => Object.entries(props.value.details).length > 0);
</script>

<template>
    <div>
        <Description v-if="!value.has_payment_gateway">
            {{ __('No payment was required for this order.') }}
        </Description>

        <div v-else>
            <div class="flex items-center">
                <Icon v-if="value.logo" :name="value.logo" class="mr-4 h-8 w-8" />

                <div class="flex flex-col space-y-1">
                    <Heading
                        :class="{ 'text-red-500 dark:text-red-950': value.invalid }"
                        v-tooltip.top="value.invalid ? __('This payment gateway is no longer installed.') : null"
                        :text="value.title"
                    />
                </div>
            </div>

            <hr v-if="hasDetails" class="my-4 border-gray-300 dark:border-gray-700" />

            <div v-if="hasDetails">
                <ul class="list-none space-y-2">
                    <li v-for="(detail, label) in value.details" :key="label">
                        <Description>
                            <strong>{{ label }}:</strong>
                            <a v-if="detail.url" :href="detail.url" target="_blank">{{ detail.text }}</a>
                            <span v-else>{{ detail }}</span>
                        </Description>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
