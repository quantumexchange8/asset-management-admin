<script setup>
import Tag from 'primevue/tag';
import { generalFormat } from '@/Composables/format';
import dayjs from 'dayjs';
import { ref } from 'vue';

const props = defineProps({
    withdrawalHistory: Object,
});

console.log(props.withdrawalHistory)

const {formatAmount} = generalFormat();

const tooltipText = ref('copy')

function copyToClipboard(text) {
    const textToCopy = text;

    const textArea = document.createElement('textarea');
    document.body.appendChild(textArea);

    textArea.value = textToCopy;
    textArea.select();

    try {
        const successful = document.execCommand('copy');

        tooltipText.value = 'copied';
        setTimeout(() => {
            tooltipText.value = 'copy';
        }, 1500);
    } catch (err) {
        console.error('Copy to clipboard failed:', err);
    }

    document.body.removeChild(textArea);
}
</script>

<template>
  <div class="flex flex-col items-center gap-4 divide-y dark:divide-surface-700 self-stretch">
        <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full">
            <div class="flex flex-col items-start w-full">
                <span class="text-surface-950 dark:text-white font-medium">{{ withdrawalHistory.user.name }}</span>
                <span class="text-surface-500 text-sm">{{ withdrawalHistory.user.email }}</span>
            </div>
            <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                $ {{ formatAmount(withdrawalHistory.amount) }}
            </div>
        </div>

        <div class="flex flex-col gap-3 items-start w-full pt-4">
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.date') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ dayjs(withdrawalHistory.created_at).format('DD/MM/YYYY HH:mm:ss') }}
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.transaction_number') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ withdrawalHistory.transaction_number }}
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.to') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ withdrawalHistory.to_payment_account_name }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    <Tag
                        :severity="withdrawalHistory.to_payment_platform === 'bank' ? 'info' : 'secondary'"
                        :value="withdrawalHistory.to_currency"
                    />
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.account_number') }}
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch relative">
                    <Tag
                        v-if="tooltipText === 'copied'"
                        class="absolute -top-1 right-[90px] md:-top-6 md:right-8 !bg-surface-950 !text-white"
                        :value="$t(`public.${tooltipText}`)"
                    ></Tag>
                    <div
                        class="break-words text-surface-950 dark:text-white text-sm font-medium hover:cursor-pointer select-none"
                        @click="copyToClipboard(withdrawalHistory.to_payment_account_no)"
                    >
                        {{ withdrawalHistory.to_payment_account_no }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.fee') }}
                </div>
                <div class="text-red-500 text-sm font-medium">
                    $ {{ formatAmount(withdrawalHistory.transaction_charges ?? 0) }}
                </div>
            </div>

            
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.receive') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    $ {{ formatAmount(withdrawalHistory.transaction_amount ?? 0) }}
                </div>
            </div>
        </div>
    </div>
</template>