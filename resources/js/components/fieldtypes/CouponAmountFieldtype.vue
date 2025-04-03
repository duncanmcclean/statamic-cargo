<template>
    <div>
        <money-fieldtype
            v-if="mode === 'fixed'"
            :handle="config.handle"
            :meta="meta.meta.money"
            :config="meta.config.money"
            :value="couponValue"
            @update:value="updateCouponValue"
        />

        <integer-fieldtype
            v-else-if="mode === 'percentage'"
            :handle="config.handle"
            :meta="meta.meta.integer"
            :config="meta.config.integer"
            :value="couponValue"
            @update:value="updateCouponValue"
        />
    </div>
</template>

<script>
import { Fieldtype } from 'statamic';

export default {
    mixins: [Fieldtype],

    inject: ['store'],

    props: ['meta'],

    data() {
        return {
            mode: null,
            couponValue: null,
            previousAmounts: {},
        };
    },

    mounted() {
        this.couponValue = this.value?.value || this.value || null;
        this.mode = this.value?.mode || this.store.values.type;

        // todo: refactor to work with pinia
        // this.$store.watch(
        //     (state) => state.publish.base.values.type,
        //     (type) => {
        //         // Keep track of the previous amount, so we can restore it when switching between modes.
        //         this.previousAmounts[this.mode] = this.couponValue;
        //
        //         this.mode = type;
        //         this.couponValue = this.previousAmounts[type] || null;
        //     },
        //     { immediate: false }
        // )

    methods: {
        updateCouponValue(value) {
            this.couponValue = value;
        },
    },

    watch: {
        couponValue(couponValue) {
            let value = {
                mode: this.mode,
                value: couponValue,
            }

            if (JSON.stringify(value) !== JSON.stringify(this.value)) {
                this.update(value);
            }
        },
    },
}
</script>