<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    }
});
</script>

<template>
    <div class="col-span-12 md:col-span-6 xl:col-span-3 shadow-lg sm:rounded-lg border-solid border dark:border-gray-400 bg-white dark:bg-gray-800 p-6">
        <div class="card h-full">
            <span class="font-semibold text-lg text-surface-900 dark:text-surface-0">
                {{ title }}
            </span>
            <div class="flex justify-between items-start mt-4">
                <div class="w-6/12">
                    <span class="text-4xl font-bold text-surface-900 dark:text-surface-0">
                        {{ value }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>