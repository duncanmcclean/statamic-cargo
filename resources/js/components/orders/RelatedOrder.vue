<script setup>
import { DateFormatter } from '@statamic/cms';
import {InlineEditForm} from '@statamic/cms/temporary';
import { Heading, Description, Badge, Button, Dropdown, DropdownMenu, DropdownItem, injectPublishContext as injectContainerContext } from '@statamic/cms/ui';
import OrderStatusBadge from "./OrderStatusBadge.vue";
import { ref, computed } from 'vue';

const { parentContainer: parentPublishContainer, name } = injectContainerContext();

const props = defineProps({
	item: Object,
	config: Object,
	statusIcon: Boolean,
	editable: Boolean,
	sortable: Boolean,
	readOnly: Boolean,
	formComponent: String,
	formComponentProps: Object,
	formStackSize: String,
});

const item = ref(props.item);
const isEditing = ref(false);
const date = computed(() => DateFormatter.format(item.value.date, 'datetime'));

function edit() {
	if (!props.editable) return;
	if (item.value.invalid) return;

	if (item.value.reference) {
		let parentContainer = parentPublishContainer;
		while (parentContainer) {
			if (parentContainer.reference.value === item.value.reference) {
				Statamic.$toast.error(__("You're already editing this item."));
				return;
			}
			parentContainer = parentContainer.parentContainer;
		}
	}

	isEditing.value = true;
}

function itemUpdated(responseData) {
	item.value.order_number = responseData.order_number;
	item.value.date = responseData.date;
	item.value.status = responseData.status;
	item.value.grand_total = responseData.grand_total;

	Statamic.$events.$emit(`live-preview.${name.value}.refresh`);
}
</script>

<template>
	<div
		class="shadow-ui-sm relative z-(--z-index-above) flex w-full h-full items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 [&:has(.cursor-grab)]:px-1.5 py-1.5 mb-1.5 last:mb-0 text-base dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
	>
		<div class="flex flex-1 items-center justify-between p-1">
			<div
				v-if="item.invalid"
				v-tooltip.top="__('ID not found')"
				v-text="__(item.title)"
				class="line-clamp-1 text-sm text-gray-600 dark:text-gray-300"
			/>

			<div v-else>
				<div class="mb-2">
					<Heading>
						<a v-if="editable" :href="item.edit_url" @click.prevent="edit">#{{ item.order_number }}</a>
						<div v-else @click.prevent="edit">#{{ item.order_number }}</div>

						<OrderStatusBadge :status="item.status" />
					</Heading>
				</div>

				<div>
					<Description :text="`${date} • ${item.grand_total}`" class="text-xs" />
				</div>
			</div>

			<div v-if="!readOnly" class="flex flex-1 items-center justify-end">
				<div class="flex items-center">
					<Dropdown>
						<template #trigger>
							<Button icon="dots" variant="ghost" size="xs" v-bind="$attrs" :aria-label="__('Open dropdown menu')" />
						</template>
						<DropdownMenu>
							<DropdownItem
								v-if="editable"
								:text="__('Edit')"
								@click="edit"
							/>
							<DropdownItem
								:text="__('Unlink')"
								variant="destructive"
								@click="$emit('removed')"
							/>
						</DropdownMenu>
					</Dropdown>
				</div>
			</div>
		</div>

		<InlineEditForm
			v-if="isEditing"
			:item="item"
			:component="formComponent"
			:component-props="formComponentProps"
			:stack-size="formStackSize"
			@updated="itemUpdated"
			@closed="isEditing = false"
		/>
	</div>
</template>