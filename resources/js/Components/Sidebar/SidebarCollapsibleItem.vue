<script setup>
import { Link } from '@inertiajs/vue3'
import {IconCircle} from "@tabler/icons-vue";
import {sidebarState} from "@/Composables/index.js";

const props = defineProps({
    href: String,
    title: String,
    active: {
        type: Boolean,
        default: false,
    },
    external: {
        type: Boolean,
        default: false,
    }
})

const { external } = props

const Tag = external ? 'a' : Link
</script>

<template>
    <li
        :class="[
            'text-sm hover:cursor-pointer hover:bg-primary-50 dark:hover:bg-transparent mt-1',
        ]"
    >
        <component
            :is="Tag"
            :href="href"
            v-bind="$attrs"
            :class="[
                'p-2.5 flex gap-3 items-center hover:text-primary-500 dark:hover:text-primary-300 w-full',
                {
                    'text-primary-500 dark:text-primary-300': active,
                    'text-surface-700 dark:text-surface-500': !active,
                },
            ]"
        >
            <div class="pl-6 flex items-center justify-center">
                <IconCircle
                    size="10"
                />
            </div>
            <div
                v-show="sidebarState.isOpen"
                :class="[
                    'font-medium',
                    {
                        'text-primary-500 dark:text-primary-300': active,
                        'text-surface-700 dark:text-surface-300': !active,
                    },
                ]"
            >
                {{ $t(`public.${title}`) }}
            </div>
        </component>
    </li>
</template>
