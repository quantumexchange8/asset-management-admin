<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar/Sidebar.vue'
import { sidebarState } from '@/Composables'
import Navbar from '@/Components/Navbar.vue';
import Toast from 'primevue/toast'
defineProps({
    title: String
})
</script>

<template>
    <Head :title="$t(`public.${title}`)"></Head>

    <div class="min-h-screen bg-surface-50 dark:bg-surface-ground">
        <!-- Sidebar -->
        <Sidebar />

        <div style="transition-property: margin; transition-duration: 150ms" :class="[
            'min-h-screen flex flex-col',
            {
                'lg:ml-[252px]': sidebarState.isOpen, //adjust navbar when sidebar open
            },
        ]">
            <!-- Navbar -->
            <Navbar :title="$t(`public.${title}`)" />

            <!-- Page Content -->
            <main class="flex flex-1 justify-center items-start px-3 pt-3 pb-12 md:px-5 md:pt-5">
                <div class="w-full">
                      <!-- Toast -->
                    <Toast />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
