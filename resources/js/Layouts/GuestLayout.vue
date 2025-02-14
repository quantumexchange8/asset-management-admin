<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {IconSun, IconMoon, IconWorld} from "@tabler/icons-vue";
import { toggleDarkMode, isDark } from '@/Composables'
import Button from "@/Components/Button.vue"
import { Head } from '@inertiajs/vue3';
import {ref} from "vue";
import {loadLanguageAsync} from "laravel-vue-i18n";
import Menu from "primevue/menu";

const props = defineProps({
    title: String
});

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
</script>

<template>
    <Head :title="$t(`public.${title}`)"></Head>

    <div class="flex flex-col min-h-screen bg-surface-0 dark:bg-surface-ground transition-all duration-200 w-full">
        <div class="flex gap-1 py-3 px-5 md:px-10 justify-end items-center">
            <Button type="button" variant="gray-text" icon-only pill @click="() => { toggleDarkMode() }">
                <IconSun v-if="!isDark" size="20" stroke-width="1.5" />
                <IconMoon v-if="isDark" size="20" color="white" stroke-width="1.5" />
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
        </div>
        <div class="flex flex-col justify-center items-center pb-12 md:px-8 w-full">
            <div class="w-full flex flex-col gap-8 justify-center items-center">
                <ApplicationLogo class="h-16 w-fit fill-current text-primary" />
                <slot />
            </div>
        </div>
    </div>

    <Menu
        ref="menu"
        id="overlay_menu"
        :model="locales"
        :popup="true"
        class="w-32"
    />
</template>
