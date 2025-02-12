<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref} from "vue";
import RebateOverview from "@/Pages/Reports/Rebate/RebateOverview.vue";
import RebateBonusTable from "@/Pages/Reports/Rebate/RebateBonusTable.vue";

const totalBonusAmount = ref(null);
const maxBonusAmount = ref(null);

const handleOverview = (data) => {
    totalBonusAmount.value = Number(data.totalBonusAmount);
    maxBonusAmount.value = Number(data.maxBonusAmount);
}
</script>

<template>
    <AuthenticatedLayout title="rebate_bonus">
        <div class="flex flex-col items-center gap-5">
            <RebateOverview
                :totalBonusAmount="totalBonusAmount"
                :maxBonusAmount="maxBonusAmount"
            />

            <RebateBonusTable
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>
