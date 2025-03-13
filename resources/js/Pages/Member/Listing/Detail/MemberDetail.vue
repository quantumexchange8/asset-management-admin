<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import Breadcrumb from 'primevue/breadcrumb';
import { ref, h, onMounted } from 'vue';
import MemberInfo from './MemberInfo.vue';
import Tabs from 'primevue/tabs';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import FinanceInfo from './Finance/FinanceInfo.vue';
import KycProfile from './KycProfile.vue';

const props = defineProps({
    user: Object,
    front_identity_image: String,
    back_identity_image: String,
    profile_photo: String,
    upline_profile_photo: String,
});

const tabs = ref([
    {
        title: 'finance',
        component: h(FinanceInfo, {
            user: props.user,
        }),
        value: '0'
    },
    {
        title: 'investment',
        value: '1'
    },
    {
        title: 'history',
        value: '2'
    },
]);

const selectedType = ref('finance');
const activeIndex = ref('0');

onMounted(() => {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    if(params.tab === 'investment'){
        selectedType.value = 'investment';
        activeIndex.value = '2';
    }
});
</script>

<template>
    <AuthenticatedLayout title="member_detail">
        <div class="flex flex-col gap-5">
            <!-- Member Info -->
            <div class="flex flex-col lg:flex-row items-center w-full gap-5 self-stretch">
                <MemberInfo
                    :user="user"
                    :profile_photo="profile_photo"
                    :upline_profile_photo="upline_profile_photo"
                />

                <KycProfile
                    :user="user"
                    :front_identity_image="front_identity_image"
                    :back_identity_image="back_identity_image"
                />
            </div>

            <!-- Tabs -->
            <div class="flex flex-col">
                <Tabs v-model:value="activeIndex">
                    <TabList>
                        <Tab v-for="tab in tabs" :key="tab.title" :value="tab.value">{{ $t(`public.${tab.title}`) }}</Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel v-for="tab in tabs" :key="tab.value" :value="tab.value">
                            <component :is="tab.component" />
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
