<template>
    <div>
        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            ref="dataList"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
            @selections-updated="selections = $event"
        >
            <div>
                <div class="card relative overflow-hidden p-0">
                    <div
                        class="dark:border-dark-900 flex flex-wrap items-center justify-between border-b px-2 pb-2 text-sm"
                    >
                        <data-list-filter-presets
                            ref="presets"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                        />

                        <data-list-search
                            class="mt-2 h-8 w-full min-w-[240px]"
                            ref="search"
                            v-model="searchQuery"
                            :placeholder="searchPlaceholder"
                        />

                        <div class="mt-2 flex space-x-2">
                            <button
                                class="btn btn-sm ltr:ml-2 rtl:mr-2"
                                v-text="__('Reset')"
                                v-show="isDirty"
                                @click="$refs.presets.refreshPreset()"
                            />
                            <button
                                class="btn btn-sm ltr:ml-2 rtl:mr-2"
                                v-text="__('Save')"
                                v-show="isDirty"
                                @click="$refs.presets.savePreset()"
                            />
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                        </div>
                    </div>
                    <div>
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :is-searching="true"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @changed="filterChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                        />
                    </div>

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <BulkActions
                        :url="actionUrl"
                        :selections="selections"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                        v-slot="{ actions }"
                    >
                        <div class="fixed inset-x-0 bottom-1 z-100 flex w-full justify-center">
                            <ButtonGroup>
                                <Button
                                    variant="primary"
                                    class="text-gray-400!"
                                    :text="__n(`:count item selected|:count items selected`, selections.length)"
                                />
                                <Button
                                    v-for="action in actions"
                                    :key="action.handle"
                                    variant="primary"
                                    :text="__(action.title)"
                                    @click="action.run"
                                />
                            </ButtonGroup>
                        </div>
                    </BulkActions>

                    <div class="overflow-x-auto overflow-y-hidden">
                        <data-list-table
                            v-show="items.length"
                            :allow-bulk-actions="true"
                            :loading="loading"
                            :sortable="true"
                            :toggle-selection-on-row-click="true"
                            @sorted="sorted"
                        >
                            <template #cell-name="{ row: discount, value }">
                                <div class="title-index-field">
                                    <a
                                        class="title-index-field inline-flex items-center"
                                        :href="discount.edit_url"
                                        @click.stop
                                    >
                                        <span
                                            class="little-dot ltr:mr-2 rtl:ml-2"
                                            v-tooltip="getStatusLabel(discount)"
                                            :class="getStatusClass(discount)"
                                            v-if="!columnShowing('status')"
                                        />
                                        <span v-text="discount.name" />
                                    </a>
                                </div>
                            </template>
                            <template #cell-status="{ row: discount }">
                                <div
                                    class="discount-status-index-field select-none"
                                    v-tooltip="getStatusTooltip(discount)"
                                    :class="`status-${discount.status}`"
                                    v-text="getStatusLabel(discount)"
                                />
                            </template>
                            <template #actions="{ row: discount, index }">
                                <ItemActions
                                    :url="actionUrl"
                                    :actions="discount.actions"
                                    :item="discount.id"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                    v-slot="{ actions }"
                                >
                                    <Dropdown placement="left-start" class="me-3">
                                        <DropdownMenu>
                                            <DropdownLabel :text="__('Actions')" />
                                            <DropdownItem
                                                :text="__('Edit')"
                                                :href="discount.edit_url"
                                                icon="edit"
                                                v-if="discount.editable"
                                            />
                                            <DropdownSeparator v-if="actions.length" />
                                            <DropdownItem
                                                v-for="action in actions"
                                                :key="action.handle"
                                                :text="__(action.title)"
                                                :icon="action.icon"
                                                :variant="action.dangerous ? 'destructive' : 'default'"
                                                @click="action.run"
                                            />
                                        </DropdownMenu>
                                    </Dropdown>
                                </ItemActions>
                            </template>
                        </data-list-table>
                    </div>
                </div>
                <data-list-pagination
                    class="mt-6"
                    :resource-meta="meta"
                    :per-page="perPage"
                    :show-totals="true"
                    @page-selected="selectPage"
                    @per-page-changed="changePerPage"
                />
            </div>
        </data-list>
    </div>
</template>

<script>
import Listing from '@statamic/components/Listing.vue';
import {
    Button,
    ButtonGroup,
    Panel,
    PanelFooter,
    StatusIndicator,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownLabel,
    DropdownSeparator,
} from '@statamic/ui';
import { BulkActions, ItemActions } from 'statamic';

export default {
    mixins: [Listing],

    components: {
        Button,
        ButtonGroup,
        Panel,
        PanelFooter,
        StatusIndicator,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
        DropdownSeparator,
        ItemActions,
        BulkActions,
    },

    data() {
        return {
            listingKey: 'discounts',
            preferencesPrefix: `cargo.discounts`,
            requestUrl: cp_url(`discounts`),
            pushQuery: true,
        };
    },

    methods: {
        getStatusClass(discount) {
            if (discount.status === 'active') {
                return 'bg-green-600';
            } else if (discount.status === 'scheduled') {
                return 'bg-amber-200 dark:bg-amber-300';
            } else if (discount.status === 'expired') {
                return 'bg-gray-400 dark:bg-dark-200';
            }
        },

        getStatusLabel(discount) {
            if (discount.status === 'active') {
                return __('Active');
            } else if (discount.status === 'scheduled') {
                return __('Scheduled');
            } else if (discount.status === 'expired') {
                return __('Expired');
            }
        },

        getStatusTooltip(discount) {
            if (discount.status === 'active') {
                return __('Active');
            } else if (discount.status === 'scheduled') {
                return __('Scheduled');
            } else if (discount.status === 'expired') {
                return __('Expired');
            }
        },

        columnShowing(column) {
            return this.visibleColumns.find((c) => c.field === column);
        },
    },
};
</script>
