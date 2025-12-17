<script setup>
import { computed } from 'vue';
import { DateFormatter } from '@statamic/cms';
import {
	Widget,
	StatusIndicator,
	Listing,
	ListingTableHead as TableHead,
	ListingTableBody as TableBody,
	ListingPagination as Pagination,
	Skeleton,
	Description,
} from '@statamic/cms/ui';
import { Link } from '@statamic/cms/inertia';

const props = defineProps({
	additionalColumns: Array,
	title: String,
	listingUrl: String,
	initialPerPage: {
		type: Number,
		default: 5,
	},
	ordersSince: String,
});

const columns = computed(() => [
	{ label: 'Order Number', field: 'order_number', visible: true },
	{ label: 'Grand Total', field: 'grand_total', visible: true },
	{ label: 'Date', field: 'date', visible: true },
	...props.additionalColumns,
]);

const widgetProps = computed(() => ({
	title: props.title,
	icon: 'shopping-cart',
	href: props.listingUrl,
}));

function formatDate(value) {
	return DateFormatter.format(value, 'date');
}

const additionalParameters = computed(() => {
	return {
		filters: utf8btoa(JSON.stringify({
			order_date: {
				operator: '>',
				value: props.ordersSince,
			}
		})),
	};
});
</script>

<template>
	<Listing
		:url="cp_url('orders')"
		:columns
		:per-page="initialPerPage"
		:show-pagination-totals="false"
		:show-pagination-page-links="false"
		:show-pagination-per-page-selector="false"
		sort-column="order_number"
		sort-direction="desc"
		:additional-parameters
	>
		<template #initializing>
			<Widget v-bind="widgetProps">
				<div class="flex flex-col justify-between px-4 py-3">
					<Skeleton v-for="i in initialPerPage" class="h-[1.25rem] mb-[0.375rem] w-full" />
				</div>
			</Widget>
		</template>
		<template #default="{ items, loading }">
			<Widget v-bind="widgetProps">
				<Description v-if="!items.length" class="flex-1 flex items-center justify-center">
					{{ __("There aren't any recent orders") }}
				</Description>
				<div class="px-4 py-3">
					<table class="w-full [&_td]:p-0.75 [&_td]:text-sm" :class="{ 'opacity-50': loading }">
						<TableHead sr-only />
						<TableBody>
							<template #cell-order_number="{ row: order }">
								<Link class="line-clamp-1 overflow-hidden text-ellipsis" :href="order.edit_url">
									<span v-text="`#${order.order_number}`" />
								</Link>
							</template>
							<template #cell-date="{ row: order, isColumnVisible }">
								<div
									class="text-end font-mono text-xs text-gray-500 antialiased min-w-0"
									v-html="formatDate(order.date.date)"
									v-if="isColumnVisible('date')"
								/>
							</template>
						</TableBody>
					</table>
				</div>
				<template #actions>
					<Pagination />
				</template>
			</Widget>
		</template>
	</Listing>
</template>