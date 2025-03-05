<script setup>
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import EditContactInfo from './EditContactInfo.vue';
import { generalFormat } from '@/Composables/format';
import Avatar from 'primevue/avatar';
import {useLangObserver} from "@/Composables/localeObserver.js";
import {
    IconUserFilled,
    IconUsersGroup
} from "@tabler/icons-vue";

const props = defineProps({
    user: Object,
    upline_profile_photo: String,
    profile_photo: String,
});

const {formatNameLabel} = generalFormat();
const {locale} = useLangObserver();
</script>

<template>
    <Card class="w-full self-stretch relative">
        <template #content>
            <div class="absolute top-3 right-3">
                <EditContactInfo
                    :memberInfo="user"
                />
            </div>
            <div class="flex flex-col gap-8 self-stretch">
                <div class="flex gap-3 md:gap-5 items-center self-stretch">
                    <div
                        class="w-12 md:w-14 h-12 md:h-14 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800 flex items-center justify-center">
                        <div v-if="user" class="w-full h-full flex justify-center items-center">
                            <Avatar
                                v-if="profile_photo"
                                :image="profile_photo"
                                shape="circle"
                                class="w-full h-full object-cover"
                            />
                            <Avatar
                                v-else
                                :label="formatNameLabel(user.name)"
                                shape="circle"
                                class="w-full h-full flex items-center justify-center text-lg md:text-xl font-bold text-white bg-primary-500"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-5 w-full">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-3">
                                <div class="text-base md:text-lg text-surface-950 dark:text-white">
                                    {{ user.name }}
                                </div>
                                <Tag
                                    :value="user.rank.rank_name === 'member' ? $t(`public.${user.rank.rank_name}`) : user.rank.rank_name"
                                    severity="info"
                                />
                            </div>

                            <div class="flex gap-1 md:gap-3 items-center self-stretch text-xs md:text-sm text-surface-400 dark:text-surface-500">
                                <span>@{{ user.username }}</span>
                                <span>|</span>
                                <span>{{ user.id_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- email -->
                        <div class="flex flex-col gap-1 items-start w-full">
                            <span class="text-xs text-surface-500">{{ $t('public.email') }}</span>
                            <div
                                class="truncate text-surface-950 dark:text-white text-sm font-medium max-w-36 md:max-w-full">
                                {{ user.email }}
                            </div>
                        </div>

                        <!-- phone -->
                        <div class="flex flex-col gap-1 items-start w-full">
                            <div class="text-surface-500 text-xs w-full">
                                {{ $t('public.phone') }}
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ user.dial_code }} {{ user.phone || '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Country -->
                        <div class="flex flex-col gap-1 items-start w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.country') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <img
                                    v-if="user.country.iso2"
                                    :src="`https://flagcdn.com/w40/${user.country.iso2.toLowerCase()}.png`"
                                    :alt="user.country.iso2"
                                    width="24"
                                    height="18"
                                />
                                <div class="max-w-[200px] truncate text-sm">{{ JSON.parse(user.country.translations)[locale] || user.country.name }}</div>
                            </div>
                        </div>

                        <!-- Referrer -->
                        <div class="flex flex-col gap-1 items-start w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.referrer') }}
                            </div>
                            <div class="flex items-center gap-2 w-full">
                                <div class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                    <Avatar
                                        v-if="props.upline_profile_photo"
                                        :image="props.upline_profile_photo"
                                        shape="circle"
                                        class="w-full h-full object-cover"
                                    />
                                    <Avatar
                                        v-else
                                        :label="formatNameLabel(user.name)"
                                        shape="circle"
                                        class="w-full h-full flex items-center justify-center text-sm md:text-xs font-bold text-white bg-primary-500"
                                    />
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ user.upline?.name ?? "-" }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Directs Count -->
                        <div class="flex flex-col gap-1 items-start w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.directs') }}
                            </div>
                            <div class="flex gap-2 items-center text-surface-950 dark:text-white text-sm font-medium w-full">
                                <IconUserFilled size="16" stroke-width="1.5" />
                                {{ user.children_count }}
                            </div>
                        </div>

                        <!-- Referer -->
                        <div class="flex flex-col gap-1 items-start w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                {{ $t('public.networks') }}
                            </div>
                            <div class="flex gap-2 items-center text-surface-950 dark:text-white text-sm font-medium w-full">
                                <IconUsersGroup size="16" stroke-width="1.5" />
                                {{ user.total_network }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
