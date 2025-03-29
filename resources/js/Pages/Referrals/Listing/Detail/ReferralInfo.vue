<script setup>
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import { generalFormat } from '@/Composables/format';
import { useLangObserver } from '@/Composables/localeObserver';

const props = defineProps({
    referral: Object,
})

const {formatNameLabel} = generalFormat();
const {locale} = useLangObserver();

</script>

<template>
    <div class="flex flex-col lg:flex-row items-center w-full gap-5 self-stretch">
        <Card class="w-full self-stretch relative">
            <template #content>
                <div class="flex flex-col gap-5 self-stretch">
                    <div class="flex gap-3 md:gap-5 items-center self-stretch">
                        <div
                            class="w-12 md:w-14 h-12 md:h-14 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800 flex items-center justify-center">
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
                                <div class="flex items-center gap-3">
                                    <div class="text-lg text-surface-950 dark:text-white">
                                        {{ props.referral.name }}
                                    </div>
                                    <Tag
                                        :value="props.referral.rank.rank_name === 'member' ? $t(`public.${props.referral.rank.rank_name}`) : props.referral.rank.rank_name"
                                        severity="info"
                                    />
                                </div>
                                <div class="flex gap-1 md:gap-3 items-center self-stretch text-xs md:text-sm text-surface-400 dark:text-surface-500">
                                    <span>@{{ props.referral.username }}</span>
                                    <span>|</span>
                                    <span>{{ props.referral.id_number }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <!-- email -->
                            <div class="flex flex-col justify-center items-start w-full">
                                <div class="flex gap-1 items-center w-full">
                                    <span class="text-xs text-surface-500">{{ $t('public.email') }}</span>
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
                            <div class="flex flex-col gap-1 items-start w-full">
                                <div class="text-surface-500 text-xs w-full truncate">
                                    {{ $t('public.country') }}
                                </div>
                                <div class="flex gap-1 items-center">
                                    <img
                                        v-if="props.referral.country?.iso2"
                                        :src="`https://flagcdn.com/w40/${props.referral.country?.iso2.toLowerCase()}.png`"
                                        :alt="props.referral.country?.iso2"
                                        width="24"
                                        height="18"
                                    />
                                    <div class="max-w-[200px] truncate text-sm">{{ JSON.parse(props.referral.country?.translations)[locale] || props.referral.country?.name }}</div>
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
                            <div class="flex flex-col items-center bg-surface-100 dark:bg-surface-800 p-2 rounded-md w-full">
                                <span class="text-md font-medium">{{ $t('public.directs') }}</span>
                                <span class="text-xl font-extrabold text-primary-500">{{props.referral.downlines_count}}</span>
                            </div>

                            <div class="flex flex-col items-center bg-surface-100 dark:bg-surface-800 p-2 rounded-md w-full">
                                <span class="text-md font-medium">{{ $t('public.networks') }}</span>
                                <span class="text-xl font-extrabold text-primary-500">{{props.referral.total_downlines_count}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>