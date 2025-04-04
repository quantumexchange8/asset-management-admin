<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref, h} from "vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import ReferralTree from './Structure/ReferralTree.vue';
import ReferralListing from "./Listing/ReferralListing.vue";
import ReferralTable from "./Listing/ReferralTable.vue";

const props = defineProps({
    parentCount: Number,
});

const tabs = ref([
    {
        title: 'structure',
        component: h(ReferralTree),
        value: '0'
    },
    {
        title: 'listing',
        component: h(ReferralTable, {
            parentCount: props.parentCount,
        }),
        value: '1'
    }
]);

const activeIndex = ref('0');
</script>

<template>
    <AuthenticatedLayout title="member_referrals">
        <Tabs v-model:value="activeIndex">
            <TabList>
                <Tab
                    v-for="tab in tabs"
                    :key="tab.title"
                    :value="tab.value"
                >
                    {{ $t(`public.${tab.title}`) }}
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel
                    v-for="tab in tabs"
                    :key="tab.value"
                    :value="tab.value"
                >
                    <component :is="tab.component" />
                </TabPanel>
            </TabPanels>
        </Tabs>
    </AuthenticatedLayout>
</template>
