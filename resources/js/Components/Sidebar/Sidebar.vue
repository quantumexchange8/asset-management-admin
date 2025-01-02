<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { sidebarState } from '@/Composables'
import SidebarHeader from '@/Components/Sidebar/SidebarHeader.vue'
import SidebarContent from '@/Components/Sidebar/SidebarContent.vue'

onMounted(() => {
    window.addEventListener('resize', sidebarState.handleWindowResize)

    router.on('navigate', () => {
        if (window.innerWidth <= 1024) {
            sidebarState.isOpen = false
        }
    })
})
</script>

<template>

    <aside style="
            transition-property: width, transform;
            transition-duration: 150ms;
        " :class="[
            'fixed inset-y-0 z-30 bg-white dark:bg-surface-900 flex flex-col border-r border-transparent dark:border-surface-700 shadow-[0_4px_16px_rgba(110,231,183,0.2)] dark:shadow-none',
            {
                'translate-x-0 w-[252px]':
                    sidebarState.isOpen,
                '-translate-x-full':
                    !sidebarState.isOpen,
            },
        ]">
        <SidebarHeader />

        <div class="flex-1 flex flex-col overflow-hidden">
            <SidebarContent />
        </div>

    </aside>
</template>
