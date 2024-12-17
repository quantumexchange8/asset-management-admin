<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';

const cards = [
    { title: 'Sales', value: 120 },
    { title: 'Revenue', value: '$3,500' },
    { title: 'Orders', value: 45 },
    { title: 'Customers', value: 240 },
];
</script>

<template>
    <AuthenticatedLayout :title="'Dashboard'">
        <div class="py-12">
            <div class="layout-content">
                <div
                    class="overflow-hidden bg-white shadow-lg sm:rounded-lg border-solid border dark:border-gray-400 dark:bg-gray-800 mb-6">
                    <div class="p-6 text-gray-950 dark:text-gray-100">
                        You're logged in!
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-8">
                    <Card v-for="(card, index) in cards" :key="index" :title="card.title" :value="card.value">
                    </Card>
                    <div
                        class="col-span-12 xl:col-span-9 shadow-lg sm:rounded-lg border-solid border dark:border-gray-400 bg-white dark:bg-gray-800 p-6">
                        <div class="card h-full">
                            <div class="flex items-start justify-between mb-12">
                                <span class="text-surface-900 dark:text-surface-0 text-xl font-semibold">
                                    Revenue Overview
                                </span>
                            </div>
                            <div class="p-chart text-surface-900 dark:text-surface-0" style="position: relative;">
                                Chart/Graph
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>