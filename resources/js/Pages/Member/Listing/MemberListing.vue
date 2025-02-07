<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMember from './AddMember.vue';
import MemberTable from './MemberTable.vue';
import MemberOverview from './MemberOverview.vue';

const props = defineProps({
    memberCounts: Number
})
</script>

<template>
    <AuthenticatedLayout title="member_list">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
            <!-- Add User Button -->
            <div class="flex justify-end w-full">
                <AddMember />
            </div>

            <MemberOverview :memberCounts="memberCounts"/>

            <!-- User Table -->
            <MemberTable />
        </div>
    </AuthenticatedLayout>
</template>
