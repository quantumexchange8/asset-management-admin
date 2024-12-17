<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { sidebarState } from '@/Composables'
import { IconX } from '@tabler/icons-vue';

</script>

<template>
    <div class="flex gap-2 pr-2 lg:pr-5 px-5 py-[18px] w-full">
        <Link :href="route('dashboard')" class="w-full flex items-center">
        <div class="flex items-center self-stretch gap-2">
            <div v-show="sidebarState.isOpen || sidebarState.isHovered" class="px-2">
                <ApplicationLogo class="block h-9 w-auto fill-current text-green-500" />
            </div>
            <div v-show="sidebarState.isOpen || sidebarState.isHovered" class="text-green-500">
                Asset-CN
            </div>
        </div>
        </Link>
    </div>
</template>
