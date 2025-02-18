<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref} from "vue";
import ProfitSharingOverview from "./ProfitSharingOverview.vue";
import ProfitSharingTable from "./ProfitSharingTable.vue";

const totalBonusAmount = ref(null);
const maxBonusAmount = ref(null);

const handleOverview = (data) => {
    totalBonusAmount.value = Number(data.totalBonusAmount);
    maxBonusAmount.value = data.maxBonusAmount;
}
</script>

<template>
    <AuthenticatedLayout title="profit_sharing">
        <div class="flex flex-col items-center gap-5">
            <ProfitSharingOverview
                :totalBonusAmount="totalBonusAmount"
                :maxBonusAmount="maxBonusAmount"
            />

            <ProfitSharingTable
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>
