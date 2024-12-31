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
import { IconUsersGroup } from '@tabler/icons-vue';

</script>

<template>
    <ScrollPanel style="flex: 1; width: 100%;" class="h-full">
        <!-- Dashboard -->
        <SidebarLink 
            :title="'Dashboard'" 
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Customer -->
        <SidebarCategoryLabel :title="'Member'" />
        <SidebarCollapsible 
            :title="'Member'"
            :active="route().current('member.*')"
        >
            <template #icon>
                <IconUsers :size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem 
                :title="'Member Listing'" 
                :href="route('member.getMemberList')"
                :active="route().current('member.getMemberList')"
            />

            <SidebarCollapsibleItem 
                :title="'Pending Kyc'" 
                :href="route('member.getPendingKyc')"
                :active="route().current('member.getPendingKyc')"
            />
        </SidebarCollapsible>
        

        <!-- Group -->
        <SidebarLink 
            :title="'Group'" 
            href="#"
        >
            <template #icon>
                <IconUsersGroup :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Transaction -->
        <SidebarCategoryLabel :title="'Transaction'" />

        <!-- Pending -->
        <SidebarCollapsible :title="'Pending'">
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem :title="'Deposit'"  href="#"/>
            <SidebarCollapsibleItem :title="'Withdrawal'"  href="#"/>
        </SidebarCollapsible>

        <!-- History -->
        <SidebarCollapsible 
            :title="'History'"
            :active="route().current('transaction.history.*')"
        >
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem 
                :title="'Deposit'"  
                :href="route('transaction.history.getDepositHistory')"
                :active="route().current('transaction.history.getDepositHistory')"
            />
            
            <SidebarCollapsibleItem 
                :title="'Withdrawal'"  
                :href="route('transaction.history.getWithdrawalHistory')"
                :active="route().current('transaction.history.getWithdrawalHistory')"
            />
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
