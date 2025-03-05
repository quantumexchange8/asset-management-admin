<script setup>
import {IconXboxX} from "@tabler/icons-vue";

defineProps({
    title: String,
    message: String,
})
</script>

<template>
    <div class="flex flex-col gap-3 items-center self-stretch py-3">
        <div class="flex items-center justify-center rounded-full bg-surface-100 dark:bg-surface-800/40 w-[72px] h-[72px]">
            <div class="flex items-center justify-center rounded-full bg-surface-200 dark:bg-surface-700 w-14 h-14 text-surface-600 dark:text-surface-300">
                <IconXboxX size="36" stroke-width="1.5" />
            </div>
        </div>

        <div class="flex flex-col gap-1 items-center self-stretch text-sm">
            <span class="font-semibold dark:text-white">{{ title }}</span>
            <span class="text-surface-500">{{ message }}</span>
        </div>
    </div>
</template>
