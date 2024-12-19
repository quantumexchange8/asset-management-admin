<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';

const cards = [
    { title: 'Sales', value: 120 },
    { title: 'Revenue', value: '$3,500' },
    { title: 'Orders', value: 45 },
    { title: 'Customers', value: 240 },
];
</script>

<template>
    <AuthenticatedLayout :title="'Dashboard'">
        <div class="py-4">
            <div class="layout-content">
                <div class="grid grid-cols-12 gap-6">
                    <!-- Cards -->
                    <Card v-for="(card, index) in cards" :key="index"
                        class="col-span-12 md:col-span-6 xl:col-span-3"
                        :title="card.title" :value="card.value">
                        <template #title>{{ card.title }}</template>
                        <template #content>{{ card.value }}</template>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>