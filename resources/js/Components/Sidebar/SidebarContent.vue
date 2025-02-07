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
    IconHomeShare,
    IconHomeDollar
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Components/Sidebar/SidebarCategoryLabel.vue";
import ScrollPanel from 'primevue/scrollpanel';
</script>

<template>
    <ScrollPanel style="flex: 1; width: 100%;" class="h-full">
        <!-- Dashboard -->
        <SidebarLink
            title="dashboard"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Member -->
        <SidebarCategoryLabel title="member" />
        <SidebarCollapsible
            title="member"
            :active="route().current('member.*')"
        >
            <template #icon>
                <IconUsers size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                title="member_listing"
                :href="route('member.getMemberList')"
                :active="route().current('member.getMemberList')"
            />
            <SidebarCollapsibleItem
                title="pending_kyc"
                :href="route('member.getPendingKyc')"
                :active="route().current('member.getPendingKyc')"
            />
            <SidebarCollapsibleItem
                title="member_referrals"
                :href="route('referral.getReferralList')"
                :active="route().current('referral.getReferralList')"
            />
        </SidebarCollapsible>

        <!-- Marketplace -->
        <SidebarCategoryLabel title="marketplace" />
        <!-- Broker -->
        <SidebarLink
            title="broker"
            :href="route('broker.getBrokerList')"
            :active="route().current('broker.getBrokerList')"
        >
            <template #icon>
                <IconHomeDollar size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Connections -->
        <SidebarCollapsible
            title="connections"
            :active="route().current('connection.*')"
        >
            <template #icon>
                <IconHomeShare size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                title="broker_connection"
                :href="route('connection.broker_connection')"
                :active="route().current('connection.broker_connection')"
            />
        </SidebarCollapsible>

        <!-- Transaction -->
        <SidebarCategoryLabel title="transaction" />

        <!-- Pending -->
        <SidebarCollapsible
            title="pending"
            :active="route().current('transaction.pending.*')"
        >
            <template #icon>
                <IconClockDollar size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                title="deposit"
                :href="route('transaction.pending.getPendingDeposit')"
            />

            <SidebarCollapsibleItem
                title="withdrawal"
                :href="route('transaction.pending.getPendingWithdrawal')"
            />
        </SidebarCollapsible>

        <!-- History -->
        <SidebarCollapsible
            title="history"
            :active="route().current('transaction.history.*')"
        >
            <template #icon>
                <IconReportMoney size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem
                title="deposit"
                :href="route('transaction.history.getDepositHistory')"
                :active="route().current('transaction.history.getDepositHistory')"
            />

            <SidebarCollapsibleItem
                title="withdrawal"
                :href="route('transaction.history.getWithdrawalHistory')"
                :active="route().current('transaction.history.getWithdrawalHistory')"
            />
        </SidebarCollapsible>

        <!-- Commissions -->
        <SidebarCategoryLabel title="reports" />

        <SidebarLink
            title="rebate_bonus"
            :href="route('report.getCommissionsList')"
            :active="route().current('report.getCommissionsList')"
        >
            <template #icon>
                <IconUserDollar size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>
    </ScrollPanel>
</template>
