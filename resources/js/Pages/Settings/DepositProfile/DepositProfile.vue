<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DepositProfileTable from './DepositProfileTable.vue';
import AddDepositProfile from './AddDepositProfile.vue';

</script>

<template>
    <AuthenticatedLayout :title="'deposit_profile'">
        <div class="space-y-4">
            <div class="flex justify-end w-full">
                <AddDepositProfile />
            </div>
            
            <DepositProfileTable />

        </div>

    </AuthenticatedLayout>
</template>