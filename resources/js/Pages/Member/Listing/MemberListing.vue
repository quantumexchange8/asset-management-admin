<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMember from './AddMember.vue';
import MemberTable from './MemberTable.vue';

defineProps({
    user: {
        type: Array,
        required: true
    },
    userCount: {
        type: Number,
    }
});
</script>

<template>
    <AuthenticatedLayout :title="'Member List'">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
            <!-- Add User Button -->
            <div class="flex justify-end w-full">
                <AddMember />
            </div>

            <!-- User Table -->
            <MemberTable 
                :user="user"
                :userCount="userCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
