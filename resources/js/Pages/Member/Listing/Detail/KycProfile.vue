<script setup>
import Card from 'primevue/card';
import InputLabel from '@/Components/InputLabel.vue';
import Image from 'primevue/image';
import Tag from "primevue/tag";
import EmptyData from "@/Components/EmptyData.vue"

const props = defineProps({
    user: Object,
    front_identity_image: String,
    back_identity_image: String,
});

const getSeverity = (status) => {
    switch (status) {
        case 'unverified':
            return 'danger';

        case 'verified':
            return 'success';

        case 'active':
            return 'success';

        case 'pending':
            return 'info';

    }
}
</script>

<template>
    <Card class="w-full self-stretch relative">
        <template #content>
            <div class="flex flex-col gap-5 w-full">
                <div class="flex items-center justify-between gap-5 self-stretch w-full">
                    <div class="flex flex-col items-start self-stretch">
                        <span class="text-xs text-surface-500">{{ $t('public.id_passport') }}</span>
                        <div>
                            {{ user.identity_number ?? '-' }}
                        </div>
                    </div>
                    <Tag
                        :value="$t(`public.${user.kyc_status}`)"
                        :severity="getSeverity(user.kyc_status)"
                    />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="front_identity">{{ $t('public.front_identity' )}}</InputLabel>
                        <div
                            class="flex flex-col gap-3 items-center self-stretch px-5 py-4 rounded-md border-2 border-dashed transition-colors duration-150 bg-surface-50 dark:bg-surface-ground border-surface-300 dark:border-surface-600"
                        >
                            <Image
                                v-if="front_identity_image"
                                role="presentation"
                                alt="front_identity_image"
                                :src="front_identity_image"
                                preview
                                imageClass="w-full object-contain h-32"
                            />
                            <div
                                v-else
                                class="h-32"
                            >
                                <EmptyData
                                    :title="$t('public.no_files_submitted')"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="front_identity">{{ $t('public.back_identity' )}}</InputLabel>
                        <div
                            class="flex flex-col gap-3 items-center self-stretch px-5 py-4 rounded-md border-2 border-dashed transition-colors duration-150 bg-surface-50 dark:bg-surface-ground border-surface-300 dark:border-surface-600"
                        >
                            <Image
                                v-if="back_identity_image"
                                role="presentation"
                                alt="back_identity_image"
                                :src="back_identity_image"
                                preview
                                imageClass="w-full object-contain h-32"
                            />
                            <div
                                v-else
                                class="h-32"
                            >
                                <EmptyData
                                    :title="$t('public.no_files_submitted')"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
