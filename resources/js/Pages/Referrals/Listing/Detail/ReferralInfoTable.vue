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

const { formatAmount } = generalFormat();

// Calculate total personal funds
const totalPersonalFunds = computed(() => {
    if (props.referral && props.referral.broker_details) {
        return props.referral.broker_details.reduce((total, broker) => {
            return total + (broker.personal_funds || 0);
        }, 0);
    }
    return 0;
});

// Calculate total team funds
const totalTeamFunds = computed(() => {
    if (props.referral && props.referral.broker_details) {
        return props.referral.broker_details.reduce((total, broker) => {
            return total + (broker.team_funds || 0);
        }, 0);
    }
    return 0;
});
</script>


<template>
 <Card class="w-full">
    <template #content>
        <div class="w-full">
            <DataTable
                :value="props.referral.broker_details"
            >
                <template #empty>
                    <EmptyData
                        :title="$t('public.no_connection')"
                        :message="$t('public.connect_with_broker')"
                    />
                </template>

                <template v-if="props.referral.broker_details.length > 0">

                    <Column 
                        field="broker"
                           
                    >
                        <template #header>
                            <span class="block">{{ $t('public.broker') }}</span>
                        </template>
                        <template #body="{ data }">
                            {{ data.broker_name }}
                        </template>
                        <template #footer>
                            <span class="font-bold">{{ $t('public.total') }}:</span>
                        </template>
                    </Column>

                    <Column 
                            field="current_asset_capital"
                           
                    >
                        <template #header>
                            <span class="block">{{ $t('public.personal_capital_fund') }} ($)</span>
                        </template>
                        <template #body="{ data }">
                            {{ formatAmount(data.personal_funds) }}
                        </template>
                        <template #footer>
                            {{ formatAmount(totalPersonalFunds) }}
                        </template>
                    </Column>

                    <Column 
                        field="current_team_capital"
                            
                    >
                        <template #header>
                            <span class="block">{{ $t('public.team_capital_fund') }} ($)</span>
                        </template>
                        <template #body="{ data }">
                            {{ formatAmount(data.team_funds) }}
                        </template>
                        <template #footer>
                            {{ formatAmount(totalTeamFunds) }}
                        </template>
                    </Column>

                  
                </template>
            </DataTable>
        </div>
    </template>
 </Card>
</template>