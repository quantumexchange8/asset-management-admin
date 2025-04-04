<script setup>
import DataTable from 'primevue/datatable';
import Card from 'primevue/card';
import Column from 'primevue/column';
import { generalFormat } from '@/Composables/format';
import EmptyData from '@/Components/EmptyData.vue';
import { computed } from 'vue';

const props = defineProps({
    referral: Object,
});
console.log(props.referral)
const { formatAmount } = generalFormat();

// const totalPersonalFunds = computed(() => {
//     if (Array.isArray(props.referral)) {
//         return props.referral.reduce((sum, user) => sum + (user.active_connections_sum_capital_fund || 0), 0);
//     }
//     return 0;
// });

// const totalTeamFunds = computed(() => {
//     if (Array.isArray(props.referral)) {
//         return props.referral.reduce((sum, user) => sum + (user.total_downline_capital_fund || 0), 0);
//     }
//     return 0;
// });

</script>


<template>
 <Card class="w-full">
    <template #content>
        <div class="w-full">
            <DataTable 
                :value="props.referral.active_connections"
            >
                <template #empty>
                    <EmptyData
                        :title="$t('public.no_connection')"
                        :message="$t('public.connect_with_broker')"
                    />
                </template>

                <template v-if="props.referral.active_connections?.length">
                    <Column 
                        field="broker"
                        style="min-width: 7rem"
                    >
                        <template #header>
                            <span class="block">{{ $t('public.broker') }}</span>
                        </template>
                        <template #body="{ data }">
                            <!-- {{ data.broker_name }} -->
                        </template>
                        <template #footer>
                            <span>{{ $t('public.total') }}:</span>
                        </template>
                    </Column>

                    <Column 
                        field="current_asset_capital"   
                        style="min-width: 9rem"
                    >
                        <template #header>
                            <span class="block">{{ $t('public.personal_capital_fund') }} ($)</span>
                        </template>
                        <template #body="{ data }">
                            {{ formatAmount(data.capital_fund) }}
                        </template>
                        <template #footer>
                            <!-- {{ formatAmount(totalPersonalFunds) }} -->
                        </template>
                    </Column>

                    <Column 
                        field="current_team_capital"
                    >
                        <template #header>
                            <span class="block">{{ $t('public.team_capital_fund') }} ($)</span>
                        </template>
                        <template #body="{ data }">
                            {{ formatAmount(data.total_downline_capital_fund) }}
                        </template>
                        <template #footer>
                            <!-- {{ formatAmount(totalTeamFunds) }} -->
                        </template>
                    </Column>
                </template>
            </DataTable>
        </div>
    </template>
 </Card>
</template>