<template>
    <div>
        <div class="receipt-table w-full">
            <div class="receipt-table-header">
                <div class="col-span-3 text-sm">{{ __('Product') }}</div>
                <div class="text-right text-sm">{{ __('Unit Price') }}</div>
                <div class="text-right text-sm">{{ __('Quantity') }}</div>
                <div class="text-right text-sm">{{ __('Total') }}</div>
            </div>
            <LineItem
                v-for="lineItem in value.line_items"
                :lineItem="lineItem"
                :key="lineItem.id"
                :form-component="meta.product.formComponent"
                :form-component-props="meta.product.formComponentProps"
                @updated="lineItemUpdated"
            />
            <div class="receipt-total dark:border-dark-500 border-t font-semibold">
                <div>{{ __('Subtotal') }}</div>
                <div>{{ value.totals.sub_total }}</div>
            </div>
            <template v-for="discount in value.discounts">
                <div class="receipt-total">
                    <div>
                        <span>{{ __('Discount') }}</span>
                        <span class="help-block mb-0">{{ discount.description }}</span>
                    </div>
                    <div>-{{ discount.amount }}</div>
                </div>
            </template>
            <div v-if="value.shipping" class="receipt-total">
                <div>
                    <span>{{ __('Shipping') }}</span>
                    <span class="help-block mb-0">{{ value.shipping.name }}</span>
                </div>
                <div>{{ value.shipping.price }}</div>
            </div>
            <div v-if="value.taxes" class="receipt-total">
                <div>
                    <span>{{ __('Taxes') }}</span>
                    <span v-for="item in value.taxes.breakdown" class="help-block mb-0"
                        >{{ item.rate }}% {{ item.description }} ({{ item.amount }})</span
                    >
                </div>
                <div>{{ value.totals.tax_total }}</div>
            </div>
            <div class="receipt-total font-bold">
                <div>{{ __('Grand Total') }}</div>
                <div>{{ value.totals.grand_total }}</div>
            </div>
            <div v-if="value.refund.issued" class="receipt-total">
                <div>{{ __('Refunded') }}</div>
                <div>-{{ value.totals.amount_refunded }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import { Fieldtype } from 'statamic';
import LineItem from './OrderReceipt/LineItem.vue';

export default {
    components: { LineItem },

    mixins: [Fieldtype],

    data() {
        return {
            receipt: this.value,
        };
    },

    methods: {
        lineItemUpdated(lineItem) {
            this.value.line_items = this.value.line_items.map((item) => {
                if (item.id === lineItem.id) {
                    return lineItem;
                }

                return item;
            });
        },
    },
};
</script>
