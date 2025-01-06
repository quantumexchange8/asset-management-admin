<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    title: String,
    message: String
});
</script>

<template>
    <div class="flex flex-col justify-center items-center py-5 px-6 gap-5 self-stretch">
        <!-- <slot name="image">
            <img src="/storage/pictures/mountain.jpg" alt="no_data" class="w-60 h-[180px]">
        </slot> -->

        <div class="flex flex-col items-center gap-2 self-stretch">
            <slot name="title">
                <div v-if="title" class="self-stretch text-surface-950 dark:text-white text-center font-semibold text-sm md:text-base">
                    {{ title }}
                </div>
            </slot>

            <slot name="message">
                <div v-if="message" class="self-stretch text-surface-400 dark:text-surface-600 text-center text-xs md:text-sm">
                    {{ message }}
                </div>
            </slot>
        </div>
    </div>
</template>
