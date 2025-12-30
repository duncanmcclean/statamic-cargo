<script setup>
import { computed } from 'vue';
import {
	Widget,
	Listing,
	ListingTableHead as TableHead,
	ListingTableBody as TableBody,
	Description,
} from '@statamic/cms/ui';
import { Link } from '@statamic/cms/inertia';

const props = defineProps({
	products: Array,
	title: String,
});

const columns = computed(() => [
	{ label: 'Product', field: 'title', visible: true },
	{ label: 'Stock', field: 'stock', visible: true },
]);

const widgetProps = computed(() => ({
	title: props.title,
	icon: 'fieldtype-entries',
}));
</script>

<template>
	<Listing :items="products" :columns>
		<template #default="{ items, loading }">
			<Widget v-bind="widgetProps">
				<Description v-if="!items.length" class="flex-1 flex items-center justify-center">
					{{ __("There aren't any low stock products") }}
				</Description>
				<div class="px-4 py-3">
					<table class="w-full [&_td]:p-0.75 [&_td]:text-sm" :class="{ 'opacity-50': loading }">
						<TableHead sr-only />
						<TableBody>
							<template #cell-title="{ row: product, value }">
								<Link class="text-ellipsis overflow-hidden" :href="product.edit_url">
									{{ value }}
								</Link>
							</template>
							<template #cell-stock="{ value }">
								<div class="text-end font-mono text-xs text-gray-500 antialiased min-w-0">
									{{ value }}
								</div>
							</template>
						</TableBody>
					</table>
				</div>
			</Widget>
		</template>
	</Listing>
</template>
