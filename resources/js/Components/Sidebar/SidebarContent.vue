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
    IconHomeDollar,
    IconBusinessplan,
    IconCoin,
    IconUserCog,
    IconAdjustmentsAlt,
    IconHomeStar,
    IconUserQuestion,
    IconUsersGroup,
    IconArrowsDiff,
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Components/Sidebar/SidebarCategoryLabel.vue";
import ScrollPanel from 'primevue/scrollpanel';
import {usePage} from "@inertiajs/vue3";
import {ref, watchEffect} from "vue";
import { usePermission } from "@/Composables/index.js";

const page = usePage();
const pendingKycCount = ref(page.props.getPendingKycCount);
const pendingDepositCounts = ref(page.props.getPendingDepositCount);
const pendingAccountCounts = ref(page.props.getPendingAccountCount);
const pendingWithdrawalCounts = ref(page.props.pendingWithdrawalCounts);
const { hasRole, hasPermission } = usePermission();

// Update pending counts
const getPendingCounts = async () => {
    try {
        const response = await axios.get(route('dashboard.getPendingCounts'));
        pendingKycCount.value = response.data.pendingKycCount;
        pendingDepositCounts.value = response.data.pendingDepositCounts;
        pendingAccountCounts.value = response.data.getPendingAccountCount;
        pendingWithdrawalCounts.value = response.data.pendingWithdrawalCounts;
    } catch (error) {
        console.error('Error pending counts:', error);
    }
};

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getPendingCounts();
    }
});
</script>

<template>
    <ScrollPanel style=" width: 100%; height: 100%;">
        <!-- Dashboard -->
        <SidebarLink
            title="dashboard"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
            v-if="hasPermission('access_dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Member -->
        <SidebarCategoryLabel
            title="member"
            v-if="hasPermission('access_member_listing') || hasPermission('access_pending_kyc') || hasPermission('access_member_referrals')"
        />

        <!-- Member Listing -->
        <SidebarLink
            title="member_listing"
            :href="route('member.getMemberList')"
            :active="route().current('member.getMemberList')"
            v-if="hasPermission('access_member_listing')"
        >
            <template #icon>
                <IconUsers size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Pending KYC -->
        <SidebarLink
            title="pending_kyc"
            :href="route('member.getPendingKyc')"
            :active="route().current('member.getPendingKyc')"
            v-if="hasPermission('access_pending_kyc')"
            :pendingCounts="pendingKycCount"
        >
            <template #icon>
                <IconUserQuestion size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Member Referrals -->
        <SidebarLink
            title="member_referrals"
            :href="route('referral')"
            :active="route().current('referral')"
            v-if="hasPermission('access_member_referrals')"
        >
            <template #icon>
                <IconUsersGroup size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Marketplace -->
        <SidebarCategoryLabel
            v-if="hasPermission('access_broker') || hasPermission('access_connections') || hasPermission('access_account_listing')"
            title="marketplace"
        />
        <!-- Broker -->
        <SidebarLink
            title="broker"
            :href="route('broker.getBrokerList')"
            :active="route().current('broker.getBrokerList')"
            v-if="hasPermission('access_broker')"
        >
            <template #icon>
                <IconHomeDollar size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Broker account -->
        <SidebarCollapsible
            title="accounts"
            :active="route().current('broker_accounts.*')"
            :pending-counts="pendingAccountCounts"
            v-if="hasPermission('access_account_listing') || hasPermission('access_pending_account')"
        >
            <template #icon>
                <IconHomeStar size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                title="account_listing"
                :href="route('broker_accounts.account_listing')"
                :active="route().current('broker_accounts.account_listing')"
                v-if="hasPermission('access_account_listing')"
            />
            <SidebarCollapsibleItem
                title="pending_account"
                :href="route('broker_accounts.pending_account')"
                :active="route().current('broker_accounts.pending_account')"
                :pending-counts="pendingAccountCounts"
                v-if="hasPermission('access_pending_account')"
            />
        </SidebarCollapsible>

        <!-- Connections -->
        <SidebarCollapsible
            title="connections"
            :active="route().current('connection.*')"
            v-if="hasPermission('access_connections')"
        >
            <template #icon>
                <IconHomeShare size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                title="broker_connection"
                :href="route('connection.broker_connection')"
                :active="route().current('connection.broker_connection')"
                v-if="hasPermission('access_connections')"
            />
<!--            <SidebarCollapsibleItem-->
<!--                title="pending_connection"-->
<!--                :href="route('connection.pending_connection')"-->
<!--                :active="route().current('connection.pending_connection')"-->
<!--                v-if="hasPermission('access_connections')"-->
<!--            />-->
        </SidebarCollapsible>

        <!-- Transaction -->
        <SidebarCategoryLabel
            title="transaction"
            v-if="hasPermission('access_pending_deposit') || hasPermission('access_pending_withdrawal') || hasPermission('access_deposit_history') || hasPermission('access_withdrawal_history')"
        />

        <!-- Pending -->
        <SidebarCollapsible
            title="pending"
            :active="route().current('transaction.pending.*')"
            :pending-counts="pendingDepositCounts || pendingWithdrawalCounts"
            v-if="hasPermission('access_pending_deposit') || hasPermission('access_pending_withdrawal')"
        >
            <template #icon>
                <IconClockDollar size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                title="deposit"
                :href="route('transaction.pending.getPendingDeposit')"
                :active="route().current('transaction.pending.getPendingDeposit')"
                :pendingCounts="pendingDepositCounts"
                v-if="hasPermission('access_pending_deposit')"
            />

            <SidebarCollapsibleItem
                title="withdrawal"
                :href="route('transaction.pending.getPendingWithdrawal')"
                :active="route().current('transaction.pending.getPendingWithdrawal')"
                :pendingCounts="pendingWithdrawalCounts"
                v-if="hasPermission('access_pending_withdrawal')"
            />
        </SidebarCollapsible>

        <!-- History -->
        <SidebarCollapsible
            title="history"
            :active="route().current('transaction.history.*')"
            v-if="hasPermission('access_deposit_history') || hasPermission('access_withdrawal_history')"
        >
            <template #icon>
                <IconReportMoney size="20" stroke-width="1.5" />
            </template>

            <SidebarCollapsibleItem
                title="deposit"
                :href="route('transaction.history.getDepositHistory')"
                :active="route().current('transaction.history.getDepositHistory')"
                v-if="hasPermission('access_deposit_history')"
            />

            <SidebarCollapsibleItem
                title="withdrawal"
                :href="route('transaction.history.getWithdrawalHistory')"
                :active="route().current('transaction.history.getWithdrawalHistory')"
                v-if="hasPermission('access_withdrawal_history')"
            />
        </SidebarCollapsible>

        <!-- Commissions -->
        <SidebarCategoryLabel
            title="reports"
            v-if="hasPermission('access_profit_sharing') || hasPermission('access_group_incentive') || hasPermission('access_rebate_bonus') || hasPermission('access_trade_history')"
        />

        <SidebarLink
            title="profit_sharing"
            :href="route('report.profit_sharing')"
            :active="route().current('report.profit_sharing')"
            v-if="hasPermission('access_profit_sharing')"
        >
            <template #icon>
                <IconBusinessplan size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarLink
            title="ib_group_incentive"
            :href="route('report.ib_group_incentive')"
            :active="route().current('report.ib_group_incentive')"
            v-if="hasPermission('access_group_incentive')"
        >
            <template #icon>
                <IconCoin size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarLink
            title="rebate_bonus"
            :href="route('report.rebate_bonus')"
            :active="route().current('report.rebate_bonus')"
            v-if="hasPermission('access_rebate_bonus')"
        >
            <template #icon>
                <IconUserDollar size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarLink
            title="trade_history"
            :href="route('report.trade_history')"
            :active="route().current('report.trade_history')"
            v-if="hasPermission('access_trade_history')"
        >
            <template #icon>
                <IconArrowsDiff size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Settings -->
        <SidebarCategoryLabel
            title="settings"
            v-if="hasRole('super_admin')"
        />

        <SidebarLink
            title="admin_listing"
            :href="route('settings.admin_listing')"
            :active="route().current('settings.admin_listing')"
            v-if="hasRole('super_admin')"
        >
            <template #icon>
                <IconUserCog size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarLink
            title="deposit_profile"
            :href="route('settings.depositProfile')"
            :active="route().current('settings.depositProfile')"
            v-if="hasRole('super_admin')"
        >
            <template #icon>
                <IconAdjustmentsAlt size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

    </ScrollPanel>
</template>
