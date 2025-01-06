<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { sidebarState } from '@/Composables'
import { IconX } from '@tabler/icons-vue';

</script>

<template>
    <div class="flex justify-center items-center gap-2 px-5 py-[18px] w-full">
        <Link :href="route('dashboard')" class="flex items-center justify-center w-full">
            <div class="flex items-center gap-2">
                <div v-show="sidebarState.isOpen || sidebarState.isHovered" class="px-2">
                    <ApplicationLogo class="block h-9 w-auto fill-current text-primary" />
                </div>
            </div>
        </Link>
    </div>
</template>
