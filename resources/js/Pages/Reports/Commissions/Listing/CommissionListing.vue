<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CommissionTable from './CommissionTable.vue';


</script>

<template>
    <AuthenticatedLayout :title="'Commission List'">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->

            <CommissionTable />

        </div>
    </AuthenticatedLayout>
</template>