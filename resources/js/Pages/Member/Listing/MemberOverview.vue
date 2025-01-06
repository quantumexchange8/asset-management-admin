<script setup>
import Card from 'primevue/card';
import { 
    IconUsers, 
    IconUserX,
    IconUserCheck } from '@tabler/icons-vue';
import { onMounted, ref } from 'vue';
import Skeleton from 'primevue/skeleton';

const props = defineProps({
    memberCounts: Number
});

const isLoading = ref(false);
const memberCounts = ref(0);
const verifiedUser = ref(0);
const unverifiedUser = ref(0);

const getMemberOverview = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/member/get_member_overview');
        memberCounts.value = response.data.memberCounts;
        verifiedUser.value = response.data.verifiedUser;
        unverifiedUser.value = response.data.unverifiedUser;
    } catch (error) {
        console.error('Error fetching recent approvals:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getMemberOverview();
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 w-full gap-3 md:gap-5">
        <!-- total members -->
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                All Members
                            </div>
                            <div class="text-3xl font-semibold">
                                <div v-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>

                                <div v-else>
                                    {{ memberCounts }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-primary-200 dark:bg-primary-700 w-14 h-14 text-primary-600 dark:text-primary-300">
                            <IconUsers size="36" stroke-width="1.5" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <!-- total verified members -->
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                Verified
                            </div>
                            <div class="text-3xl font-semibold">
                                <div v-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>

                                <div v-else>
                                    {{ verifiedUser }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-green-200 dark:bg-green-700 w-14 h-14 text-green-600 dark:text-green-300">
                            <IconUserCheck size="36" stroke-width="1.5" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <!-- total unverified members -->
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                Unverified
                            </div>
                            <div class="text-3xl font-semibold">
                                <div v-if="isLoading">
                                    <Skeleton width="5rem" class="mt-1.5 mb-1" height="2rem"></Skeleton>
                                </div>

                                <div v-else>
                                    {{ unverifiedUser }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-red-200 dark:bg-red-700 w-14 h-14 text-red-600 dark:text-red-200">
                            <IconUserX size="36" stroke-width="1.5" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>