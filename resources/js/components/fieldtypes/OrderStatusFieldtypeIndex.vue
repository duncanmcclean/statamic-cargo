<script setup>
import { IndexFieldtype } from '@statamic/cms';
import OrderStatusBadge from "../orders/OrderStatusBadge.vue";

const props = defineProps(IndexFieldtype.props);
</script>

<template>
	<OrderStatusBadge :status="props.value" />
</template>
