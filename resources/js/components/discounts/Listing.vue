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
        >
            <div>
                <div class="card relative overflow-hidden p-0">
                    <div
                        class="flex flex-wrap items-center justify-between border-b px-2 pb-2 text-sm dark:border-dark-900"
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

                    <data-list-bulk-actions :url="actionUrl" @started="actionStarted" @completed="actionCompleted" />
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
                                        {{ discount.name }}
                                    </a>
                                </div>
                            </template>

                            <template #cell-value="{ row: discount, value }">
                                <div>
                                    {{ discount.discount_text }}
                                </div>
                            </template>

                            <template #actions="{ row: discount, index }">
                                <dropdown-list placement="left-start">
                                    <dropdown-item
                                        :text="__('Edit')"
                                        :redirect="discount.edit_url"
                                        v-if="discount.editable"
                                    />
                                    <div class="divider" v-if="discount.actions.length" />
                                    <data-list-inline-actions
                                        :item="discount.id"
                                        :url="actionUrl"
                                        :actions="discount.actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </dropdown-list>
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

export default {
    mixins: [Listing],

    data() {
        return {
            listingKey: 'discounts',
            preferencesPrefix: `cargo.discounts`,
            requestUrl: cp_url(`discounts`),
            pushQuery: true,
        };
    },
};
</script>
