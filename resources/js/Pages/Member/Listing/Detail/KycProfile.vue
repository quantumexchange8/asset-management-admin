<script setup>
import Card from 'primevue/card';
import Image from 'primevue/image';

const props = defineProps({
    user: Object,
    currentImage: String,
});
</script>

<template>
    <Card class="w-full self-stretch relative">
        <template #content>
            <div class="flex flex-row gap-4 h-full">
                <!-- First Section: IC/Passport (Front) -->
                <div class="flex-1 flex flex-col justify-center items-center">
                    <div class="text-lg text-surface-950 dark:text-white mb-4">
                        IC/Passport (Front)
                    </div>
                    <div class="w-80 h-56 flex justify-center items-center">  <!-- Slightly increased size -->
                        <Image 
                            :src="currentImage || '/storage/pictures/mountain.jpg'" 
                            alt="Image" 
                            width="350"  
                            preview 
                        />
                    </div>
                </div>

                <!-- Second Section: IC/Passport (Back) -->
                <div class="flex-1 flex flex-col justify-center items-center">
                    <div class="text-lg text-surface-950 dark:text-white mb-4">
                        IC/Passport (Back)
                    </div>
                    <div class="w-80 h-56 flex justify-center items-center">  <!-- Slightly increased size -->
                        <Image 
                            :src="currentImage || '/storage/pictures/mountain.jpg'" 
                            alt="Image" 
                            width="350"  
                            preview 
                        />
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
