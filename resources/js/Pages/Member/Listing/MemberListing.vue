<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMember from './AddMember.vue';
import MemberTable from './MemberTable.vue';

</script>

<template>
    <AuthenticatedLayout :title="'Member List'">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
            <!-- Add User Button -->
            <div class="flex justify-end w-full">
                <AddMember />
            </div>

            <!-- User Table -->
            <MemberTable />
        </div>
    </AuthenticatedLayout>
</template>
