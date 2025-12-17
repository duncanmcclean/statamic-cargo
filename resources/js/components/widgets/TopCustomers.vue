<script setup>
import { computed } from 'vue';
import {
	Widget,
	Listing,
	ListingTableHead as TableHead,
	ListingTableBody as TableBody,
	Description,
	Avatar,
} from '@statamic/cms/ui';
import { Link } from '@statamic/cms/inertia';

const props = defineProps({
	additionalColumns: Array,
	topCustomers: Array,
	title: String,
	listingUrl: String,
});

const columns = computed(() => [
	{ label: 'Email', field: 'email', visible: true },
	{ label: 'Orders Count', field: 'orders_count', visible: true },
	...props.additionalColumns,
]);

const widgetProps = computed(() => ({
	title: props.title,
	icon: 'fieldtype-users',
	href: props.listingUrl,
}));
</script>

<template>
	<Listing :items="topCustomers" :columns>
		<template #default="{ items, loading }">
			<Widget v-bind="widgetProps">
				<Description v-if="!items.length" class="flex-1 flex items-center justify-center">
					{{ __("There aren't any top customers") }}
				</Description>
				<div class="px-4 py-3">
					<table class="w-full [&_td]:p-0.75 [&_td]:text-sm" :class="{ 'opacity-50': loading }">
						<TableHead sr-only />
						<TableBody>
							<template #cell-email="{ row: user, value }">
								<Link class="text-ellipsis flex items-center gap-x-2" :href="user.edit_url">
									<Avatar :user />
									{{ value }}
								</Link>
							</template>
							<template #cell-orders_count="{ value }">
								<div
									class="text-end font-mono text-xs text-gray-500 antialiased min-w-0"
									v-html="__(':count orders', { count: value })"
								/>
							</template>
						</TableBody>
					</table>
				</div>
			</Widget>
		</template>
	</Listing>
</template>