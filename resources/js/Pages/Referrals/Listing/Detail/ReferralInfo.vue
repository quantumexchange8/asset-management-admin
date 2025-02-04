<script setup>
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import Image from 'primevue/image';

const props = defineProps({
    referral: Object,
    refereeCount: Number,
    totalDownline: Number,
})

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
            <div class="flex flex-col gap-5 self-stretch">
                <div class="flex flex-col md:flex-row gap-3 md:gap-5 items-center self-stretch">
                    <div
                        class="w-20 md:w-28 h-20 md:h-28 grow-0 shrink-0 rounded-full overflow-hidden bg-primary-200 dark:bg-surface-800">
                        <div>
                            <div v-if="props.referral">
                                <div
                                    class="w-full h-full p-2 flex justify-center items-center bg-primary-200 dark:bg-surface-800">
                                    <Image
                                        :src="referral.profile_photo ? referral.profile_photo : 'https://img.freepik.com/free-icon/referral_318-159711.jpg'"
                                        alt="referralPic" />
                                </div>
                            </div>
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
                                <span class="text-xs text-surface-500">Email Address</span>
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
                                Phone
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
                                Country
                            </div>
                            <div class="flex gap-1 items-center">
                                <span class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                    {{props.referral.country?.name || '-' }}</span>
                            </div>
                        </div>

                        <!-- Referrer -->
                        <div class="flex flex-col justify-center items-start gap-1 w-full">
                            <div class="text-surface-500 text-xs w-full truncate">
                                Referrer
                            </div>
                            <div class="flex items-center gap-2 w-full">
                                <div class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                    <div v-if="props.referral.upline_profile_photo">
                                        <img :src="props.referral.upline_profile_photo" alt="Profile Photo" />
                                    </div>
                                    <div v-else class="w-6 h-6 grow-0 shrink-0 rounded-full overflow-hidden">
                                        <Image
                                            :src="referral.profile_photo ? referral.profile_photo : 'https://img.freepik.com/free-icon/referral_318-159711.jpg'"
                                            alt="referralPic" />
                                    </div>
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
                                Rank
                            </div>
                            <div class="truncate text-surface-950 dark:text-white text-sm font-medium w-full">
                                {{ props.referral.rank.rank_name }}
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

    <Card class="w-full self-stretch relative">
    <template #content>
        <div class="flex flex-col items-center gap-16 p-10">
            <!-- Total Direct Client -->
            <div class="flex flex-col items-center">
                <span class="text-lg font-medium">Total Direct Client</span>
                <span class="text-4xl font-extrabold text-primary-500">{{props.refereeCount}}</span> <!-- Replace 25 with dynamic value -->
            </div>

            <!-- Total Downline -->
            <div class="flex flex-col items-center">
                <span class="text-lg font-medium">Total Downline</span>
                <span class="text-4xl font-extrabold text-primary-500">{{props.totalDownline}}</span> <!-- Replace 100 with dynamic value -->
            </div>
        </div>
    </template>
</Card>

</template>