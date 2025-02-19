<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import Breadcrumb from 'primevue/breadcrumb';
import { ref, h } from 'vue';
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
    refereeCount: Number,
    kycImages: Array,
});

const home = ref({
    label: 'Member Listing',
    route: route('member.getMemberList')

});

const items = ref([
    { label: props.user.name },
]);

const tabs = ref([
    {
        title: 'Finance',
        content: 'Tab 1 Content',
        component: h(FinanceInfo, {
            user: props.user,
        }),
        value: '0'
    },
    {
        title: 'Investment',
        content: 'Tab 2 Content',
        value: '1'
    },
    {
        title: 'History',
        content: 'Tab 3 Content',
        value: '2'
    },
]);
</script>

<template>
    <AuthenticatedLayout :title="'member_listing'">
        <div class="flex flex-col gap-5">
            <Breadcrumb :home="home" :model="items">
                <template #item="{ item }">
                    <Link v-if="item.route" :href="item.route">
                    <span :class="[item.icon, 'text-color']" />
                    <span class="text-primary font-semibold hover:text-primary-600">{{ item.label }}</span>
                    </Link>
                    <span v-else class="text-surface-700 dark:text-surface-0">{{ item.label }} - {{ 'Details' }}</span>
                </template>
            </Breadcrumb>

            <!-- Member Info -->

            <div class="flex flex-col lg:flex-row items-center w-full gap-5 self-stretch">
                <MemberInfo 
                    :user="user" 
                    :refereeCount="refereeCount"
                />

                <KycProfile
                    :user="user" 
                    :kycImages="kycImages"
                />
               
            </div>

            <!-- Tabs -->
            <div class="card">
                <Tabs value="0">
                    <TabList>
                        <Tab v-for="tab in tabs" :key="tab.title" :value="tab.value">{{ tab.title }}</Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel v-for="tab in tabs" :key="tab.content" :value="tab.value">
                            <component :is="tab.component" />
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>