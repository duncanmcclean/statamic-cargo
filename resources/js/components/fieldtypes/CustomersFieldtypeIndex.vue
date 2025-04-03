<template>
    <div>
        <div class="flex items-center">
            <div class="flex-1">
                <div class="truncate">
                    <a
                        v-if="value.type === 'user' && value.viewable"
                        :href="value.edit_url"
                        target="_blank"
                        class="truncate"
                    >
                        {{ value.name }}
                    </a>
                    <a v-else-if="value.type === 'guest'" class="truncate">
                        {{ value.name }}
                        <div class="status-index-field status-draft ml-2 select-none">{{ __('Guest') }}</div>
                    </a>
                    <div v-else v-text="value.name" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { IndexFieldtype } from 'statamic';

export default {
    mixins: [IndexFieldtype],
};
</script>
