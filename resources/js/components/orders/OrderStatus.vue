<script setup>
import { ref, onMounted } from 'vue';
import { Button, Field, Select, Input, Description, Textarea } from '@statamic/cms/ui';
import OrderStatusBadge from "./OrderStatusBadge.vue";

const emit = defineEmits(['update:modelValue', 'update:trackingNumber', 'update:cancellationReason']);

const props = defineProps({
    orderId: String,
    statuses: Array,
    packingSlipUrl: String,
    modelValue: String,
    trackingNumber: { type: String, required: false },
    cancellationReason: { type: String, required: false },
});

const status = ref(props.modelValue);
const trackingNumber = ref(props.trackingNumber);
const cancellationReason = ref(props.cancellationReason);
const updating = ref(false);

function update() {
    emit('update:modelValue', status.value);
    emit('update:trackingNumber', trackingNumber.value);
    emit('update:cancellationReason', cancellationReason.value);
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
    <div v-if="!updating" class="flex flex-col gap-y-4">
        <div class="flex items-center justify-between">
	        <OrderStatusBadge :status="status" size="lg" />
            <Button :text="__('Change')" size="sm" @click="updating = true" />
        </div>

        <Description v-if="trackingNumber" :text="__('Tracking Number: :trackingNumber', { trackingNumber })" />

        <Description v-if="cancellationReason" :text="__('Cancellation Reason: :cancellationReason', { cancellationReason })" />
    </div>

    <div v-else class="flex flex-col space-y-6">
        <Field label="Status">
            <Select class="w-full" :options="statuses" v-model:modelValue="status" />
        </Field>

        <Field v-if="status === 'shipped'" :label="__('Tracking Number')">
            <Input v-model:modelValue="trackingNumber" />
        </Field>

        <Field v-if="status === 'cancelled'" :label="__('Cancellation Reason')">
            <Textarea v-model:modelValue="cancellationReason" />
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
