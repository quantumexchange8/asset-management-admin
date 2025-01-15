<script setup>
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import EditContactInfo from './EditContactInfo.vue';
import Image from 'primevue/image';

const props = defineProps({
    user: Object,
    refereeCount: Number,
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
            <div class="absolute top-3 right-3">
                <EditContactInfo 
                    :memberInfo="user"
                />
            </div>
            <div class="flex flex-col gap-5 self-stretch">
                <div class="flex flex-col md:flex-row gap-3 md:gap-5 items-center self-stretch">
                    <div
                        class="w-20 md:w-28 h-20 md:h-28 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800">
                        <div>
                            <div v-if="props.user">
                                <div
                                    class="w-full h-full p-2 flex justify-center items-center bg-primary-200 dark:bg-surface-800">
                                    <Image
                                        :src="user.profile_photo ? user.profile_photo : 'https://img.freepik.com/free-icon/user_318-159711.jpg'"
                                        alt="userPic" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-5 w-full">
                        <div class="flex flex-col gap-1">
                            <div class="text-lg text-surface-950 dark:text-white">
                                {{ props.user.name }}
                            </div>

                            <div class="flex items-center self-stretch text-sm text-surface-500 dark:text-surface-500">
                                <span>{{ props.user.id_number }}</span>
                                <Divider layout="vertical" />
                                <span>{{ props.user.username }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2 flex-wrap items-center">
                            <Tag :value="props.user.status" :severity="getSeverity(props.user.status)" />

                            <Tag v-if="props.user.role !== 'user'" :value="props.user.role" severity="secondary" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- email -->
                        <div class="flex flex-col justify-center items-start w-full">
                            <div class="flex gap-1 items-center w-full">
                                <span class="text-xs text-surface-500">Email Address</span>
                                <Tag class="text-xxs" :value="props.user.email_verified_at ? 'verified' : 'unverified'"
                                    :severity="getSeverity(props.user.email_verified_at ? 'active' : 'unverified')" />
                            </div>
                            <div
                                class="truncate text-surface-950 dark:text-white text-sm font-medium max-w-36 md:max-w-full">
                                {{ props.user.email }}
                            </div>
                        </div>

                        <!-- phone -->
                        <div class="flex flex-col justify-center gap-2 items-start w-full">
                            <div class="text-surface-500 text-xs w-full">
                                Phone
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ props.user.dial_code }} {{ props.user.phone }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Country -->
                        <div class="flex flex-col justify-center items-start gap-1 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                Country
                            </div>
                            <div class="flex gap-1 items-center">
                                <span class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">{{
                                    props.user.country.name }}</span>
                            </div>
                        </div>

                        <!-- Referrer -->
                        <div class="flex flex-col justify-center items-start gap-1 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                Referrer
                            </div>
                            <div class="flex items-center gap-2 w-full">
                                <div class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                    <div v-if="props.user.upline_profile_photo">
                                        <img :src="props.user.upline_profile_photo" alt="Profile Photo" />
                                    </div>
                                    <div v-else class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                        <Image
                                            :src="user.profile_photo ? user.profile_photo : 'https://img.freepik.com/free-icon/user_318-159711.jpg'"
                                            alt="userPic" />
                                    </div>
                                </div>
                                <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{ props.user.upline?.name ?? "-" }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center gap-5 self-stretch">
                        <!-- Rank -->
                        <div class="flex flex-col justify-center items-start gap-2 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                Rank
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ props.user.rank.rank_name }}
                            </div>
                        </div>

                        <!-- Referer -->
                        <div class="flex flex-col justify-center items-start gap-2 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                Referee
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                               {{ props.refereeCount }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>