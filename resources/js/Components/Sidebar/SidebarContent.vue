<script setup>
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import {
    IconLayoutDashboard,
    IconUsers,
    IconClockDollar, 
    IconReportMoney,
    IconUserDollar,
    IconSitemap
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

        <!-- member -->
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
                :title="'Pending KYC'" 
                :href="route('member.getPendingKyc')"
                :active="route().current('member.getPendingKyc')"
            />
        </SidebarCollapsible>
        

        <!-- Broker -->
        <SidebarLink 
            :title="'Broker'" 
            :href="route('broker.getBrokerList')"
            :active="route().current('broker.getBrokerList')"
        >
            <template #icon>
                <IconUsersGroup :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- referrals -->
        <SidebarCategoryLabel :title="'Referrals'" />
        <SidebarCollapsible 
            :title="'Referrals'"
            :active="route().current('referral.*')"
        >
            <template #icon>
                <IconSitemap :size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem 
                :title="'Referrals Tree'" 
                :href="route('referral.getReferralList')"
                :active="route().current('referral.getReferralList')"
            />
        </SidebarCollapsible>

        <!-- Transaction -->
        <SidebarCategoryLabel :title="'Transaction'" />

        <!-- Pending -->
        <SidebarCollapsible 
            :title="'Pending'"
            :active="route().current('transaction.pending.*')"
        >
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem 
                :title="'Deposit'"  
                :href="route('transaction.pending.getPendingDeposit')"
            />

            <SidebarCollapsibleItem 
                :title="'Withdrawal'"  
                :href="route('transaction.pending.getPendingWithdrawal')"
            />
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

        <!-- Commissions -->
        <SidebarCategoryLabel :title="'Reports'"/>

        <SidebarCollapsible
            :title="'Commissions'"
            :active="route().current('report.*')"
        >
            <template #icon>
                <IconUserDollar :size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem 
                :title="'Commission Listing'"
                :href="route('report.getCommissionsList')"
                :active="route().current('report.getCommissionsList')"
            />

        </SidebarCollapsible>
    </ScrollPanel>
</template>
