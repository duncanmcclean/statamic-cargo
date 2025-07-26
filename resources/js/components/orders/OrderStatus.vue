<script setup>
import { computed, ref, onMounted } from 'vue';
import { Badge, Button, Field, Select, Input, Description } from '@statamic/ui';

const emit = defineEmits(['update:modelValue', 'update:trackingNumber']);

const props = defineProps({
    orderId: String,
    statuses: Array,
    packingSlipUrl: String,
    modelValue: String,
    trackingNumber: {
        type: String,
        default: null,
    },
});

const status = ref(props.modelValue);
const trackingNumber = ref(props.trackingNumber);
const updating = ref(false);

const statusLabel = computed(() => props.statuses.find((status) => status.value === props.modelValue)?.label);

const statusBadgeColour = computed(() => {
    let colors = {
        payment_pending: 'default',
        payment_received: 'blue',
        shipped: 'green',
        returned: 'orange',
        cancelled: 'red',
    };

    return colors[props.modelValue];
});

function update() {
    emit('update:modelValue', status.value);
    emit('update:trackingNumber', trackingNumber.value);
    updating.value = false;
}

onMounted(() => {
    Statamic.$commandPalette.add({
        text: __('Print Packing Slip'),
        icon: 'download',
        when: () => props.packingSlipUrl && (props.modelValue === 'shipped' || status.value === 'shipped'),
        action: () => window.open(props.packingSlipUrl, '_blank'),
    })
});
</script>

<template>
    <div v-if="!updating">
        <div class="flex items-center justify-between">
            <Badge :text="statusLabel" size="lg" flat :color="statusBadgeColour" />
            <Button :text="__('Change')" size="sm" @click="updating = true" />
        </div>

        <Description
            v-if="trackingNumber"
            class="mt-2"
            :text="__('Tracking Number: :trackingNumber', { trackingNumber })"
        />
    </div>

    <div v-else class="flex flex-col space-y-6">
        <Field label="Status">
            <Select class="w-full" :options="statuses" v-model:modelValue="status" />
        </Field>

        <Field v-if="status === 'shipped'" label="Tracking Number">
            <Input v-model:modelValue="trackingNumber" />
        </Field>

        <div v-if="status === 'returned' || status === 'cancelled'">
            <Description :text="__('Any payment will need to be refunded manually.')" />
        </div>

        <div class="flex w-full items-center justify-between">
            <Button :text="__('Update')" @click="update" />

            <Button
                v-if="status === 'shipped'"
                icon="download"
                size="sm"
                variant="ghost"
                target="_blank"
                :text="__('Print Packing Slip')"
                :href="packingSlipUrl"
            />
        </div>
    </div>
</template>
