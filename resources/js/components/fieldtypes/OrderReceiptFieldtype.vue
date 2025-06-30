<template>
    <div>
        <Table>
            <TableColumns>
                <TableColumn>{{ __('Product') }}</TableColumn>
                <TableColumn>{{ __('Unit Price') }}</TableColumn>
                <TableColumn>{{ __('Quantity') }}</TableColumn>
                <TableColumn class="text-right">{{ __('Total') }}</TableColumn>
            </TableColumns>
            <TableRows>
                <LineItemRow
                    v-for="lineItem in value.line_items"
                    :lineItem="lineItem"
                    :key="lineItem.id"
                    :form-component="meta.product.formComponent"
                    :form-component-props="meta.product.formComponentProps"
                    @updated="lineItemUpdated"
                />
                <TableRow>
                    <TableCell />
                    <TableCell />
                    <TableCell class="font-medium">{{ __('Subtotal') }}</TableCell>
                    <TableCell class="text-right">{{ value.totals.sub_total }}</TableCell>
                </TableRow>
                <TableRow v-for="discount in value.discounts">
                    <TableCell />
                    <TableCell />
                    <TableCell>
                        <strong class="mb-1 block font-medium">{{ __('Discount') }}</strong>
                        <Description :text="discount.description" />
                    </TableCell>
                    <TableCell class="text-right">-{{ discount.amount }}</TableCell>
                </TableRow>
                <TableRow v-if="value.shipping">
                    <TableCell />
                    <TableCell />
                    <TableCell>
                        <strong class="mb-1 block font-medium">{{ __('Shipping') }}</strong>
                        <Description :text="value.shipping.name" />
                    </TableCell>
                    <TableCell class="text-right">{{ value.shipping.price }}</TableCell>
                </TableRow>
                <TableRow v-if="value.taxes">
                    <TableCell />
                    <TableCell />
                    <TableCell>
                        <strong class="mb-1 block font-medium">{{ __('Taxes') }}</strong>
                        <Description
                            v-for="item in value.taxes.breakdown"
                            class="mb-1"
                            :text="`${item.rate}% ${item.description} (${item.amount})`"
                        />
                    </TableCell>
                    <TableCell class="text-right">{{ value.totals.tax_total }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell />
                    <TableCell />
                    <TableCell class="font-bold">{{ __('Grand Total') }}</TableCell>
                    <TableCell class="text-right font-bold">{{ value.totals.grand_total }}</TableCell>
                </TableRow>
                <TableRow v-if="value.refund.issued">
                    <TableCell />
                    <TableCell />
                    <TableCell class="font-medium">{{ __('Refunded') }}</TableCell>
                    <TableCell class="text-right">{{ value.totals.amount_refunded }}</TableCell>
                </TableRow>
            </TableRows>
        </Table>
    </div>
</template>

<script>
import { Fieldtype } from 'statamic';
import { Table, TableColumns, TableColumn, TableRows, TableRow, TableCell, Description } from '@statamic/ui';
import LineItemRow from './OrderReceipt/LineItemRow.vue';

export default {
    mixins: [Fieldtype],

    components: {
        Table,
        TableColumns,
        TableColumn,
        TableRows,
        TableRow,
        TableCell,
        Description,
        LineItemRow,
    },

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
