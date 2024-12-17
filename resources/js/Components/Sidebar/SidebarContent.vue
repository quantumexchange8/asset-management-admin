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
        <SidebarLink :title="'Dashboard'">
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" class="text-green-500"/>
            </template>
        </SidebarLink>

        <!-- Customer -->
        <SidebarCategoryLabel :title="'Customer'" />
        <SidebarCollapsible :title="'Customer'">
            <template #icon>
                <IconUsers :size="20" stroke-width="1.5" class="text-green-500"/>
            </template>

            <SidebarCollapsibleItem :title="'Customer List'" />

        </SidebarCollapsible>

        <!-- Ranks -->
        <SidebarCategoryLabel :title="'Ranks'" />
        <SidebarLink :title="'Ranks'">
            <template #icon>
                <IconBadges :size="20" stroke-width="1.5" class="text-green-500"/>
            </template>
        </SidebarLink>

        <!-- Transaction -->
        <SidebarCategoryLabel :title="'Transaction'" />
        <SidebarCollapsible :title="'Transaction'">
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" class="text-green-500"/>
            </template>

            <SidebarCollapsibleItem :title="'Transaction List'" />

        </SidebarCollapsible>

        <SidebarCategoryLabel
            :title="'Settings'"
        />
        <!-- Deposit Profile -->
        <SidebarLink :title="'Deposit Profile'">
            <template #icon>
                <IconReportMoney :size="20" stroke-width="1.5" class="text-green-500"/>
            </template>
        </SidebarLink>
    </ScrollPanel>
</template>
