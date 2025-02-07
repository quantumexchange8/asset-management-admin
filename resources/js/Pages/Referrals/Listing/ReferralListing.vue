<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReferralTree from './ReferralTree.vue';


</script>

<template>
    <AuthenticatedLayout title="member_referrals">
        <div class="space-y-4">
            <ReferralTree />
        </div>
    </AuthenticatedLayout>
</template>
