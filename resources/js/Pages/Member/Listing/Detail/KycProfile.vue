<script setup>
import Card from 'primevue/card';
import Galleria from 'primevue/galleria';
import Image from 'primevue/image';
import { ref, computed } from 'vue';

const props = defineProps({
    user: Object,
    kycImages: Array, // This should be an array of image URLs
});

// Use a placeholder image if kycImages is empty
const imagesToDisplay = computed(() => {
    return props.kycImages.length > 0
        ? props.kycImages
        : ['/image-not-found.jpg']; // Replace with the path to your placeholder image
});
</script>

<template>
    <Card class="w-full self-stretch relative">
        <template #content>
            <div class="flex flex-wrap gap-4 h-full justify-center">
                <div class="flex flex-col gap-1 items-start self-stretch w-full">
                    <div class="text-lg text-surface-950 dark:text-white mb-4">
                            {{ $t('public.id_passport') }}
                    </div>
                    <!-- Galleria Component -->
                    <Galleria 
                        :value="imagesToDisplay"
                        :numVisible="5"
                        :circular="true"
                        :showThumbnails="false"
                        :showIndicators="true"
                        :showItemNavigators="true"
                        :changeItemOnIndicatorHover="true"
                        :showIndicatorsOnItem="true"
                        indicatorsPosition="bottom"
                        container-class="w-full"
                    >
                        <!-- Template for displaying individual images -->
                        <template #item="slotProps">
                            <Image 
                                :src="slotProps.item" 
                                alt="Image" 
                                imageClass="w-full h-[350px] object-contain"
                                class="w-full"
                                preview
                            />
                        </template>
                    </Galleria>
                </div>
            </div>
        </template>
    </Card>
</template>
