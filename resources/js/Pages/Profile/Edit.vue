<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <AuthenticatedLayout title="profile">
        <div class="flex flex-col gap-5 items-center w-full">
            <div class="flex flex-col md:flex-row md:items-start gap-5 self-stretch w-full">
                <Card class="w-full">
                    <template #title>
                        <div class="flex flex-col">
                            <div class="flex gap-2 items-center">
                                {{ $t('public.profile_information') }}
                            </div>
                            <span class="text-sm text-surface-500">{{ $t('public.profile_information_caption') }}</span>
                        </div>
                    </template>
                    <template #content>
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </template>
                </Card>

                <Card class="w-full">
                    <template #title>
                        <div class="flex flex-col">
                            <div class="flex gap-2 items-center">
                                <span>{{ $t('public.update_password') }}</span>
                            </div>
                            <span class="text-sm text-surface-500">{{ $t('public.update_password_caption') }}</span>
                        </div>
                    </template>

                    <template #content>
                        <UpdatePasswordForm />
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
