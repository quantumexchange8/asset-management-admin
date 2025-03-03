<script setup>
import Card from 'primevue/card';
import InputLabel from '@/Components/InputLabel.vue';
import Image from 'primevue/image';
import { ref, computed } from 'vue';

const props = defineProps({
    user: Object,
    front_identity_image: String,
    back_identity_image: String,
});


</script>

<template>
    <Card class="w-full self-stretch relative">
        <template #content>
            <div class="flex flex-col gap-5 w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="front_identity">{{ $t('public.front_identity' )}}</InputLabel>
                        <div
                            class="flex flex-col gap-3 items-center self-stretch px-5 py-8 rounded-md border-2 border-dashed transition-colors duration-150 bg-surface-50 dark:bg-surface-ground border-surface-300 dark:border-surface-600"
                        >
                            <Image
                                role="presentation"
                                alt="front_identity_image"
                                :src="front_identity_image || '/image-not-found.jpg'"
                                preview
                                imageClass="w-full object-contain h-40"
                            />

                        </div>
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="front_identity">{{ $t('public.back_identity' )}}</InputLabel>
                        <div
                            class="flex flex-col gap-3 items-center self-stretch px-5 py-8 rounded-md border-2 border-dashed transition-colors duration-150 bg-surface-50 dark:bg-surface-ground border-surface-300 dark:border-surface-600"
                        >
                            <Image
                                role="presentation"
                                alt="back_identity_image"
                                :src="back_identity_image || '/image-not-found.jpg'"
                                preview
                                imageClass="w-full object-contain h-40"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
