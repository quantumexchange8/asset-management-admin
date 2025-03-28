<script setup>
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import Image from 'primevue/image';
import Avatar from 'primevue/avatar';
import { generalFormat } from '@/Composables/format';
const props = defineProps({
    referral: Object,
})

const {formatNameLabel} = generalFormat();

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
    <div class="flex flex-col lg:flex-row items-center w-full gap-5 self-stretch">
        <Card class="w-full self-stretch relative">
            <template #content>
                <div class="flex flex-col gap-5 self-stretch">
                    <div class="flex flex-col md:flex-row gap-3 md:gap-5 items-center self-stretch">
                        <div
                            class="w-20 md:w-28 h-20 md:h-28 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800">
                            <div v-if="referral" class="w-full h-full flex justify-center items-center">
                                <Avatar
                                    v-if="referral.profile_photo"
                                    :image="referral.profile_photo"
                                    shape="circle"
                                    class="w-full h-full object-cover"
                                />
                                <Avatar
                                    v-else
                                    :label="formatNameLabel(referral.name)"
                                    shape="circle"
                                    class="w-full h-full flex items-center justify-center text-lg md:text-xl font-bold text-white bg-primary-500"
                                />
                            </div>
                        </div>
    
                        <div class="flex flex-col gap-5 w-full">
                            <div class="flex flex-col gap-1">
                                <div class="text-lg text-surface-950 dark:text-white">
                                    {{ props.referral.name }}
                                </div>
    
                                <div class="flex items-center self-stretch text-sm text-surface-500 dark:text-surface-500">
                                    <span>{{ props.referral.id_number }}</span>
                                    <Divider layout="vertical" />
                                    <span>{{ props.referral.username }}</span>
                                </div>
                            </div>
    
                            <div class="flex gap-2 flex-wrap items-center">
                                <Tag :value="props.referral.status" :severity="getSeverity(props.referral.status)" />
    
                                <Tag v-if="props.referral.role !== 'referral'" :value="props.referral.role" severity="secondary" />
                            </div>
                        </div>
                    </div>
    
                    <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- email -->
                            <div class="flex flex-col justify-center items-start w-full">
                                <div class="flex gap-1 items-center w-full">
                                    <span class="text-xs text-surface-500">{{ $t('public.email') }}</span>
                                    <Tag class="text-xxs" :value="props.referral.email_verified_at ? 'verified' : 'unverified'"
                                        :severity="getSeverity(props.referral.email_verified_at ? 'active' : 'unverified')" />
                                </div>
                                <div
                                    class="truncate text-surface-950 dark:text-white text-sm font-medium max-w-36 md:max-w-full">
                                    {{ props.referral.email }}
                                </div>
                            </div>
    
                            <!-- phone -->
                            <div class="flex flex-col justify-center gap-2 items-start w-full">
                                <div class="text-surface-500 text-xs w-full">
                                    {{ $t('public.phone') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ props.referral.dial_code }} {{ props.referral.phone || '-' }}
                                </div>
                            </div>
                        </div>
    
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- Country -->
                            <div class="flex flex-col justify-center items-start gap-1 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.country') }}
                                </div>
                                <div class="flex gap-1 items-center">
                                    <span class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                        {{props.referral.country?.name || '-' }}</span>
                                </div>
                            </div>
    
                            <!-- Referrer -->
                            <div class="flex flex-col justify-center items-start gap-1 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referrer') }}
                                </div>
                                <div class="flex items-center gap-2 w-full">
                                    <div class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                        <Avatar
                                            v-if="referral.upline_profile_photo"
                                            :image="referral.upline_profile_photo"
                                            shape="circle"
                                            class="w-full h-full object-cover"
                                        />
                                        <Avatar
                                            v-else
                                            :label="formatNameLabel(referral.upline?.name)"
                                            shape="circle"
                                            class="w-full h-full flex items-center justify-center text-sm md:text-xs font-bold text-white bg-primary-500"
                                        />
                                    </div>
                                    <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                        {{ props.referral.upline?.name ?? "-" }}
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- Rank -->
                            <div class="flex flex-col justify-center items-start gap-2 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.rank') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ props.referral.rank.rank_name }}
                                </div>
                            </div>
    
                            <!-- Referer -->
                            <div class="flex flex-col justify-center items-start gap-2 w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.referee') }}
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ props.referral.downlines_count }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    
        <Card class="w-full self-stretch relative">
            <template #content>
                <div class="flex flex-col items-center gap-16 p-10">
                    <!-- Total Direct Client -->
    
                    <div class="flex flex-col items-center bg-surface-100 dark:bg-surface-800 p-5 rounded-md w-full">
                        <span class="text-lg font-medium">{{ $t('public.direct_downlines') }}</span>
                        <span class="text-4xl font-extrabold text-primary-500">{{props.referral.downlines_count}}</span> <!-- Replace 25 with dynamic value -->
                    </div>
            
                    <!-- Total Downline -->
                    <div class="flex flex-col items-center bg-surface-100 dark:bg-surface-800 p-5 rounded-md w-full">
                        <span class="text-lg font-medium">{{ $t('public.total_downlines') }}</span>
                        <span class="text-4xl font-extrabold text-primary-500">{{props.referral.total_downlines_count}}</span> <!-- Replace 100 with dynamic value -->
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>