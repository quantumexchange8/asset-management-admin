<script setup>
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import {
    IconLayoutDashboard,
    IconUsers,
    IconClockDollar,
    IconBadges,
    IconReportMoney
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Components/Sidebar/SidebarCategoryLabel.vue";
import ScrollPanel from 'primevue/scrollpanel';

</script>

<template>
    <ScrollPanel style="flex: 1; width: 100%;" class="h-full">
        <!-- Dashboard -->
        <SidebarLink 
            :title="'Dashboard'" 
            :href="route('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Customer -->
        <SidebarCategoryLabel :title="'Group'" />
        <SidebarCollapsible :title="'Group'">
            <template #icon>
                <IconUsers :size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem :title="'Group Listing'" href="#" />
        </SidebarCollapsible>

        <!-- Transaction -->
        <SidebarCategoryLabel :title="'Transaction'" />
        <SidebarCollapsible :title="'Transaction'">
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem :title="'Transaction Listing'"  href="#"/>
        </SidebarCollapsible>

        <SidebarCategoryLabel :title="'Settings'" />
        <!-- Deposit Profile -->
        <SidebarLink 
            :title="'Deposit Profile'"
            href="#"
        >
            <template #icon>
                <IconReportMoney :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>
    </ScrollPanel>
</template>
