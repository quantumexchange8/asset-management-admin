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
            <div class="flex flex-wrap gap-4 h-full justify-center">
                <!-- First Section: IC/Passport (Front) -->
                <div class="flex flex-col justify-center items-center text-center">
                    <div class="text-lg text-surface-950 dark:text-white mb-4">
                        IC/Passport (Front)
                    </div>
                    <div class="w-[350px] h-[195px] rounded-lg overflow-hidden">
                        <Image 
                            :src="currentImage || '/storage/pictures/mountain.jpg'" 
                            alt="IC/Passport Front" 
                            class="w-full h-full object-cover" 
                            preview 
                        />
                    </div>
                </div>

                <!-- Second Section: IC/Passport (Back) -->
                <div class="flex flex-col justify-center items-center text-center">
                    <div class="text-lg text-surface-950 dark:text-white mb-4">
                        IC/Passport (Back)
                    </div>
                    <div class="w-[350px] h-[195px] rounded-lg overflow-hidden">
                        <Image 
                            :src="currentImage || '/storage/pictures/mountain.jpg'" 
                            alt="IC/Passport Back" 
                            class="w-full h-full object-cover" 
                            preview 
                        />
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
