<script setup>
import { IndexFieldtype } from '@statamic/cms';
import { Badge } from '@statamic/cms/ui';

const props = defineProps(IndexFieldtype.props);
</script>

<template>
    <div>
        <a v-if="value.type === 'user' && value.viewable" :href="value.edit_url" target="_blank" class="truncate">
            {{ value.name }}
        </a>
        <a v-else-if="value.type === 'guest'" class="truncate">
            {{ value.name }}
            <Badge pill size="sm" :text="__('Guest')" />
        </a>
        <div v-else v-text="value.name" />
    </div>
</template>
