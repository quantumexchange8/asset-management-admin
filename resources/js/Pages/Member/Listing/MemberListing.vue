<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMember from './AddMember.vue';
import MemberTable from './MemberTable.vue';
import MemberOverview from './MemberOverview.vue';
import { ref } from 'vue';

const totalRecords = ref(null);
const verifiedUser = ref(null);
const unverifiedUser = ref(null);

const handleOverview = (data) => {
    totalRecords.value = data.totalRecords;
    verifiedUser.value = data.verifiedUser;
    unverifiedUser.value = data.unverifiedUser;
}
</script>

<template>
    <AuthenticatedLayout title="member_listing">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
            <!-- Add User Button -->
            <div class="flex justify-end w-full">
                <AddMember />
            </div>

            <MemberOverview
                :totalRecords="totalRecords"
                :verifiedUser="verifiedUser"
                :unverifiedUser="unverifiedUser"
            />

            <!-- User Table -->
            <MemberTable 
                @updateTotalUser="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>
