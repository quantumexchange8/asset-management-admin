<script setup>
import FinanceOverview from './FinanceOverview.vue';
import FinanceTable from './FinanceTable.vue';
import FinanceWallet from './FinanceWallet.vue';

const props = defineProps({
    user: Object,
    financeTableCount: Number,
});

</script>

<template>
    <div class="flex flex-col xl:flex-row items-start gap-5 self-stretch">
        <!-- Left Side (Total Cards + Wallet Cards) -->
        <div class="flex flex-col gap-5 w-full xl:max-w-[640px]">

            <!-- Total Cards -->
            <FinanceOverview 
               :user="user"
            />

            <!-- Wallet Cards -->
            <FinanceWallet 
                :user="user" 
            />
        </div>

        <!-- (Accumulated Amount Table) -->
        <FinanceTable 
            :user="user"
            :financeTableCount="financeTableCount"
        />
    </div>
</template>