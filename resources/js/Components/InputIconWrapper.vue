<template>
    <div
        class="relative text-gray-400 dark:text-gray-500 focus-within:text-primary-500 dark:focus-within:text-primary-400 w-full"
    >
        <div
            aria-hidden="true"
            class="absolute left-3 top-1/2 transform -translate-y-1/2 z-20"
        >
            <slot name="icon" />
        </div>

        <slot />
    </div>
</template>
