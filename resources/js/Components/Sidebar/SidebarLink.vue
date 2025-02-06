<script setup>
import { Link } from '@inertiajs/vue3'
import { sidebarState } from '@/Composables'
import {IconCircle} from "@tabler/icons-vue";
import Badge from 'primevue/badge';

const props = defineProps({
    href: {
        type: String,
        required: false,
    },
    active: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    external: {
        type: Boolean,
        default: false,
    },
    pendingCounts: Number
})

const Tag = !props.external ? Link : 'a'
</script>

<template>
    <!-- non-collapsible link -->
    <component
        :is="Tag"
        v-if="href"
        :href="href"
        :class="[
            'p-2.5 px-7 flex gap-3 items-center transition-colors w-full hover:bg-primary-50 dark:hover:bg-surface-800',
            {
                'text-surface-700 dark:text-surface-0 hover:text-primary-500':
                    !active,
                'text-primary-600 bg-primary-100 dark:text-primary-400 dark:bg-transparent':
                    active,
            },
        ]"
    >
        <div
            :class="[
                'max-w-5',
                {
                    'text-primary-500 dark:text-primary-300': active,
                    'text-surface-700 dark:text-surface-500': !active,
                },
            ]"
        >
            <slot name="icon">
                <IconCircle
                    size="20"
                />
            </slot>
        </div>

        <div class="flex items-center gap-2 w-full">
            <span
                class="text-sm font-medium w-full"
                v-show="sidebarState.isOpen || sidebarState.isHovered"
            >
                {{ $t(`public.${title}`) }}
            </span>
            <Badge
                v-if="pendingCounts > 0 && (sidebarState.isOpen || sidebarState.isHovered)"
                :value="pendingCounts"
                severity="info"
            ></Badge>
        </div>
    </component>

    <!-- collapsible link -->
    <button
        v-else
        type="button"
        :class="[
            'p-2.5 px-7 flex gap-3 items-center transition-colors w-full hover:bg-primary-50 dark:hover:bg-surface-800',
            {
                'text-surface-700 dark:text-surface-400 hover:text-primary-500':
                    !active,
                'text-primary-600 bg-primary-100 dark:text-primary-400 dark:bg-transparent':
                    active,
            },
        ]"
    >
        <div
            :class="[
                'max-w-5',
                {
                    'text-primary-500 dark:text-primary-300': active,
                    'text-surface-700 dark:text-surface-500': !active,
                },
            ]"
        >
            <slot name="icon">
                <IconCircle
                    size="20"
                />
            </slot>
        </div>

        <span
            class="text-sm font-medium dark:text-white"
            v-show="sidebarState.isOpen || sidebarState.isHovered"
        >
            {{ $t(`public.${title}`) }}
        </span>
        <slot name="arrow" />
    </button>
</template>
