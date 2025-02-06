<script setup>
import { isDark, sidebarState, toggleDarkMode } from '@/Composables'
import {
    IconLogout,
    IconMenu2,
    IconMoon,
    IconSun,
    IconWorld,
} from '@tabler/icons-vue';
import Menu from 'primevue/menu';
import { router } from "@inertiajs/vue3";
import Button from '@/Components/Button.vue';
import { Link } from '@inertiajs/vue3';
import {ref} from "vue";
import {loadLanguageAsync} from "laravel-vue-i18n";

defineProps({
    title: String
})

const menu = ref();
const locales = ref([
    {
        label: 'English',
        command: () => {
            changeLanguage('en');
        }
    },
    {
        label: '中文',
        command: () => {
            changeLanguage('cn')
        }
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const changeLanguage = async (langVal) => {
    try {
        await loadLanguageAsync(langVal);
        await axios.get(`/locale/${langVal}`);
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

const handleLogOut = () => {
    router.post(route('logout'))
}
</script>

<template>
    <nav aria-label="secondary"
        class="sticky top-0 z-30 py-2 px-3 md:px-5 bg-surface-50 dark:bg-surface-ground flex items-center gap-3">
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
            <img class="w-full h-full object-cover rounded-full" :src="('/mountain.jpg')"
                alt="Profile" />
            </Link>

            <Button type="button" variant="gray-text" icon-only pill @click="() => { toggleDarkMode() }">
                <IconSun v-if="!isDark" size="20" stroke-width="1.5" />
                <IconMoon v-if="isDark" size="20" stroke-width="1.5" />
            </Button>

            <Button
                type="button"
                variant="gray-text"
                icon-only
                @click="toggle"
                aria-haspopup="true"
                aria-controls="overlay_menu"
                pill
            >
                <IconWorld size="20" stroke-width="1.5" />
            </Button>

            <Button external type="button" variant="gray-text" icon-only pill @click="handleLogOut">
                <IconLogout size="20" stroke-width="1.5" />
            </Button>
        </div>
    </nav>
    <Menu
        ref="menu"
        id="overlay_menu"
        :model="locales"
        :popup="true"
        class="w-32"
    />
</template>
