<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block font-medium text-sm text-surface-700 dark:text-surface-300">
        <span v-if="value">
            {{ value }}<span class="text-red-500 text-sm font-medium"> *</span>
        </span>
        <span v-else>
            <slot />
        </span>
    </label>
</template>
