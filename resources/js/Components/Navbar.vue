<script setup>
import { isDark, sidebarState, toggleDarkMode } from '@/Composables'
import {
    IconLogout,
    IconMenu2,
    IconMoon,
    IconSun
} from '@tabler/icons-vue';
import { router } from "@inertiajs/vue3";
import Button from '@/Components/Button.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String
})

const handleLogOut = () => {
    router.post(route('logout'))
}
</script>

<template>
    <nav aria-label="secondary"
        class="sticky top-0 z-30 py-2 px-3 md:px-5 bg-primary-50 dark:bg-surface-950 flex items-center gap-3">
        <Button type="button" variant="gray-text" icon-only pill @click="sidebarState.isOpen = !sidebarState.isOpen">
            <IconMenu2 size="20" stroke-width="1.25" />
        </Button>
        <div class="font-semibold text-gray-700 dark:text-gray-300 w-full">
            {{ title }}
        </div>
        <div class="flex items-center">
            <Link
                class="w-11 h-11 p-1 flex items-center justify-center rounded-full hover:cursor-pointer dark:hover:bg-gray-800 md:block"
                :href="route('profile.edit')">
            <img class="w-full h-full object-cover rounded-full" :src="('storage/pictures/mountain.jpg')"
                alt="Profile" />
            </Link>

            <Button type="button" variant="gray-text" icon-only pill @click="() => { toggleDarkMode() }">
                <IconSun v-if="!isDark" size="20" stroke-width="1.5" />
                <IconMoon v-if="isDark" size="20" stroke-width="1.5" />
            </Button>

            <Button external type="button" variant="gray-text" icon-only pill @click="handleLogOut">
                <IconLogout size="20" stroke-width="1.5" />
            </Button>
        </div>
    </nav>
</template>
