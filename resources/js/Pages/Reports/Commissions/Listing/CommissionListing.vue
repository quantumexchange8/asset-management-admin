<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CommissionTable from './CommissionTable.vue';


</script>

<template>
    <AuthenticatedLayout title="commission_listing">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->

            <CommissionTable />

        </div>
    </AuthenticatedLayout>
</template>