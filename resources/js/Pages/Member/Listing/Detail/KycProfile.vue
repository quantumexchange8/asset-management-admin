<script setup>
import Card from 'primevue/card';
import Galleria from 'primevue/galleria';
import { ref, computed } from 'vue';

const props = defineProps({
    user: Object,
    kycImages: Array, // This should be an array of image URLs
});

const responsiveOptions = ref([
    {
        breakpoint: '991px',
        numVisible: 4
    },
    {
        breakpoint: '767px',
        numVisible: 3
    },
    {
        breakpoint: '575px',
        numVisible: 1
    }
]);

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
                <div class="flex flex-col justify-center items-center text-center">
                    <div class="text-lg text-surface-950 dark:text-white mb-4">
                            ID/Passport
                    </div>
                    <!-- Galleria Component -->
                    <Galleria 
                        :value="imagesToDisplay" 
                        :responsiveOptions="responsiveOptions" 
                        :numVisible="5" 
                        :circular="true"
                        containerStyle="max-width: 640px" 
                        :showItemNavigators="true" 
                        :showThumbnails="false"
                    >
                        <!-- Template for displaying individual images -->
                        <template #item="slotProps">
                            <img 
                                :src="slotProps.item" 
                                alt="Image Preview" 
                                style="width: 100%; display: block;" 
                            />
                        </template>
                    </Galleria>
                </div>
            </div>
        </template>
    </Card>
</template>
