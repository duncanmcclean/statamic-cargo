<script setup>
import { TableRow, TableCell } from '@statamic/cms/ui';
import { InlineEditForm } from '@statamic/cms';
import { ref } from 'vue';

const emit = defineEmits(['updated']);

const props = defineProps({
    lineItem: Object,
    formComponent: String,
    formComponentProps: Object,
});

const isEditing = ref(false);

function edit() {
    isEditing.value = true;
}

function itemUpdated(responseData) {
    emit('updated', {
        ...props.lineItem,
        product: {
            ...props.lineItem.product,
            title: responseData.title,
            published: responseData.published,
            private: responseData.private,
            status: responseData.status,
        },
    });
}
</script>

<template>
    <TableRow>
        <TableCell>
            <div
                v-if="lineItem.product.invalid"
                v-tooltip.top="__('A product with this ID could not be found')"
                v-text="lineItem.product.title"
            />

            <a v-else @click.prevent="edit" :href="lineItem.product.edit_url">
                {{ lineItem.product.title }}
                <span v-if="lineItem.variant" class="text-sm" v-text="`(${lineItem.variant.name})`"></span>
            </a>
        </TableCell>
        <TableCell>{{ lineItem.unit_price }}</TableCell>
        <TableCell>{{ lineItem.quantity }}</TableCell>
        <TableCell class="text-right">{{ lineItem.sub_total }}</TableCell>

        <InlineEditForm
            v-if="isEditing"
            :item="lineItem.product"
            :component="formComponent"
            :component-props="formComponentProps"
            @updated="itemUpdated"
            @closed="isEditing = false"
        />
    </TableRow>
</template>
