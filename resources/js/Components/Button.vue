<script setup>
import { toRefs, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator(value) {
            return [
                'primary-flat', 'primary-tonal', 'primary-outlined', 'primary-text',
                'gray-flat', 'gray-tonal', 'gray-outlined', 'gray-text',
                'error-flat', 'error-tonal', 'error-outlined', 'error-text',
                'success-flat', 'success-tonal', 'success-outlined', 'success-text',
            ].includes(value)
        },
    },
    type: {
        type: String,
        default: 'submit',
    },
    size: {
        type: String,
        default: 'base',
        validator(value) {
            return ['sm', 'base', 'lg'].includes(value)
        },
    },
    squared: {
        type: Boolean,
        default: false,
    },
    pill: {
        type: Boolean,
        default: false,
    },
    href: {
        type: String,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    iconOnly: {
        type: Boolean,
        default: false,
    },
    srText: {
        type: String || undefined,
        default: undefined,
    },
    external: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits(['click'])

const { type, variant, size, squared, pill, href, iconOnly, srText, external } = props

const { disabled } = toRefs(props)

const baseClasses = [
    'inline-flex items-center justify-center gap-3 transition-colors text-sm font-medium select-none disabled:cursor-not-allowed disabled:text-surface-400 dark:disabled:text-surface-500 focus:outline-none focus:ring',
]

const variantClasses = (variant) => ({
    'bg-primary-500 dark:bg-primary-600 hover:bg-primary-600 dark:hover:bg-primary-700 border border-primary-500 hover:border-primary-600 dark:border-primary-600 dark:hover:border-primary-700 focus:ring-primary-500 dark:ring-primary-600 text-white disabled:border-gray-100 disabled:bg-gray-100 dark:disabled:border-gray-800 dark:disabled:bg-gray-800': variant === 'primary-flat',
    'bg-primary-50 dark:bg-primary-950 hover:bg-primary-100 dark:hover:bg-primary-900 border border-primary-50 hover:border-primary-100 dark:border-primary-950 dark:hover:border-primary-900 focus:ring-primary-100 dark:ring-primary-900 text-primary-500 dark:text-primary-300 disabled:border-gray-100 disabled:bg-gray-100 dark:disabled:border-gray-800 dark:disabled:bg-gray-800': variant === 'primary-tonal',
    'bg-transparent hover:bg-primary-50 dark:hover:bg-primary-900 border border-primary-500 hover:border-primary-600 dark:border-primary-600 dark:hover:text-surface-300 dark:hover:border-primary-700 focus:ring-primary-500 dark:ring-primary-600 text-primary-500 dark:text-primary-400 shadow-input disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:border-gray-200 dark:disabled:border-gray-800': variant === 'primary-outlined',
    'bg-transparent dark:bg-transparent hover:bg-primary-50 dark:hover:bg-primary-950 focus:ring-primary-100 dark:ring-primary-200 text-primary-500 dark:text-primary-600 dark:hover:text-primary-400 disabled:bg-transparent dark:disabled:bg-transparent': variant === 'primary-text',

    'bg-surface-400 dark:bg-surface-800 hover:bg-surface-500 dark:hover:bg-surface-700 border border-surface-400 hover:border-surface-800 dark:border-surface-800 dark:hover:border-surface-700 focus:ring-surface-500 dark:ring-surface-600 text-white disabled:border-gray-100 disabled:bg-surface-100 dark:disabled:border-surface-800 dark:disabled:bg-surface-800': variant === 'gray-flat',
    'bg-surface-100 dark:bg-surface-950 hover:bg-surface-200 dark:hover:bg-surface-900 border border-surface-100 hover:border-surface-200 dark:border-surface-700 dark:hover:border-surface-700 focus:ring-surface-100 dark:ring-surface-700 text-surface-500 dark:text-surface-300 disabled:border-surface-100 disabled:bg-surface-100 dark:disabled:border-surface-800 dark:disabled:bg-surface-800': variant === 'gray-tonal',
    'bg-transparent hover:bg-surface-50 dark:hover:bg-surface-950 border border-surface-300 hover:border-surface-400 dark:border-surface-700 dark:hover:border-surface-500 focus:ring-surface-300 dark:ring-surface-400 text-surface-950 dark:text-surface-300 dark:hover:text-white shadow-input disabled:bg-surface-100 dark:disabled:bg-surface-800 disabled:border-surface-200 dark:disabled:border-surface-800': variant === 'gray-outlined',
    'bg-transparent dark:bg-transparent hover:bg-surface-200 dark:hover:bg-surface-800 focus:ring-0 focus:ring-surface-100 dark:ring-surface-800 text-surface-500 dark:text-white disabled:bg-transparent dark:disabled:bg-transparent': variant === 'gray-text',

    'bg-error-500 dark:bg-error-600 hover:bg-error-600 dark:hover:bg-error-700 border border-error-500 hover:border-error-600 dark:border-error-600 dark:hover:border-error-700 focus:ring-error-500 dark:ring-error-600 text-white disabled:border-gray-100 disabled:bg-gray-100 dark:disabled:border-gray-800 dark:disabled:bg-gray-800': variant === 'error-flat',
    'bg-error-50 dark:bg-error-100 hover:bg-error-100 dark:hover:bg-error-200 border border-error-50 hover:border-error-100 dark:border-error-100 dark:hover:border-error-200 focus:ring-error-100 dark:ring-error-200 text-error-500 dark:text-error-400 disabled:border-gray-100 disabled:bg-gray-100 dark:disabled:border-gray-800 dark:disabled:bg-gray-800': variant === 'error-tonal',
    'bg-white dark:bg-transparent hover:bg-error-50 dark:hover:bg-error-950 border border-error-500 hover:border-error-600 dark:border-error-600 dark:hover:border-error-700 focus:ring-error-500 dark:ring-error-600 text-error-500 dark:text-error-400 shadow-input disabled:bg-surface-100 dark:disabled:bg-surface-800 disabled:border-surface-200 dark:disabled:border-surface-800': variant === 'error-outlined',
    'bg-transparent dark:bg-transparent hover:bg-error-50 dark:hover:bg-error-950 focus:ring-error-100 dark:ring-error-200 text-error-500 dark:text-error-400 disabled:bg-transparent dark:disabled:bg-transparent': variant === 'error-text',

    'bg-success-500 dark:bg-success-600 hover:bg-success-600 dark:hover:bg-success-700 border border-success-500 hover:border-success-600 dark:border-success-600 dark:hover:border-success-700 focus:ring-success-500 dark:ring-success-600 text-white dark:text-surface-0 disabled:border-gray-100 disabled:bg-gray-100 dark:disabled:border-gray-800 dark:disabled:bg-gray-800': variant === 'success-flat',
    'bg-success-50 dark:bg-success-100 hover:bg-success-100 dark:hover:bg-success-200 border border-success-50 hover:border-success-100 dark:border-success-100 dark:hover:border-success-200 focus:ring-success-100 dark:ring-success-200 text-success-500 dark:text-success-400 disabled:border-gray-100 disabled:bg-gray-100 dark:disabled:border-gray-800 dark:disabled:bg-gray-800': variant === 'success-tonal',
    'bg-white dark:bg-surface-950 hover:bg-success-50 dark:hover:bg-success-100 border border-success-500 hover:border-success-600 dark:border-success-600 dark:hover:border-success-700 focus:ring-success-500 dark:ring-success-600 text-success-500 dark:text-success-400 shadow-input disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:border-gray-200 dark:disabled:border-gray-800': variant === 'success-outlined',
    'bg-transparent dark:bg-transparent hover:bg-success-50 dark:hover:bg-success-100 focus:ring-success-100 dark:ring-success-200 text-success-500 dark:text-success-400 disabled:bg-transparent dark:disabled:bg-transparent': variant === 'success-text',
})

const classes = computed(() => [
    ...baseClasses,
    iconOnly
        ? {
            'p-2': size === 'sm',
            'p-3': size === 'base',
            'p-4': size === 'lg',
        }
        : {
            'px-3 py-1': size === 'sm',
            'px-4 py-2': size === 'base',
            'px-8 py-4': size === 'lg',
        },
    variantClasses(variant),
    {
        'rounded-md': !squared && !pill,
        'rounded-full': pill,
    },
    {
        'pointer-events-none': href && disabled.value,
    },
])

const iconSizeClasses = computed(() => ({
    'w-4 h-4': props.size === 'sm',
    'w-5 h-5': props.size === 'base' || props.size === 'lg',
}))

const handleClick = (e) => {
    if (disabled.value) {
        e.preventDefault()
        e.stopPropagation()
        return
    }
    emit('click', e)
}

const Tag = external ?  'a' : Link
</script>

<template>
    <component
        :is="Tag"
        v-if="href"
        :href="!disabled ? href : null"
        :class="classes"
        :aria-disabled="disabled.toString()"
    >
        <span
            v-if="srText"
            class="sr-only"
        >
            {{ srText }}
        </span>

        <slot :iconSizeClasses="iconSizeClasses" />
    </component>

    <button
        v-else
        :type="type"
        :class="classes"
        @click="handleClick"
        :disabled="disabled"
    >
        <span
            v-if="srText"
            class="sr-only"
        >
            {{ srText }}
        </span>

        <slot :iconSizeClasses="iconSizeClasses" />
    </button>
</template>
