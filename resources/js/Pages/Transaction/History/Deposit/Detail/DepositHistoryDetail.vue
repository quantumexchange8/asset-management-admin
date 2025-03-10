<script setup>
import Tag from 'primevue/tag';
import Galleria from 'primevue/galleria';
import Image from 'primevue/image';
import { generalFormat } from '@/Composables/format';
import dayjs from 'dayjs';
import { ref } from 'vue';

const props = defineProps({
    depositHistory: Object,
})

console.log(props.depositHistory)

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
                <span class="text-surface-950 dark:text-white font-medium">{{ depositHistory.user.name }}</span>
                <span class="text-surface-500 text-sm">{{ depositHistory.user.email }}</span>
            </div>
            <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                $ {{ formatAmount(depositHistory.amount) }}
            </div>
        </div>

        <div class="flex flex-col gap-3 items-start w-full pt-4">
            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.request_date') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ dayjs(depositHistory.created_at).format('DD/MM/YYYY HH:mm:ss') }}
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.transaction_number') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ depositHistory.transaction_number }}
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.wallet') }}
                </div>
                <div class="text-surface-950 dark:text-white text-sm font-medium">
                    {{ $t(`public.${depositHistory.to_wallet.type}`) }}
                </div>
            </div>

            <div
                v-if="depositHistory.to_payment_platform === 'crypto'"
                class="flex flex-col md:flex-row md:items-center gap-1 self-stretch"
            >
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.txn_hash') }}
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch relative">
                    <Tag
                        v-if="tooltipText === 'copied'"
                        class="absolute -top-1 right-[90px] md:-top-6 md:right-8 !bg-surface-950 !text-white"
                        :value="$t(`public.${tooltipText}`)"
                    ></Tag>
                    <div
                        class="break-words text-surface-950 dark:text-white text-sm font-medium hover:cursor-pointer select-none"
                        @click="copyToClipboard(depositHistory.txn_hash)"
                    >
                        {{ depositHistory.txn_hash }}
                    </div>
                </div>
            </div>
        </div>

        <div v-if="depositHistory.transaction_type === 'deposit' && depositHistory.media.length > 0" class="flex flex-col md:flex-row md:items-start gap-1 self-stretch pt-5">
            <div class="w-[140px] text-surface-500 text-xs font-medium">
                {{ $t('public.payment_slip') }}
            </div>
            <div class="flex gap-2 col-span-2 items-center self-stretch w-full">
                <Galleria
                    :value="depositHistory.payment_slips"
                    :numVisible="5"
                    :circular="true"
                    :showThumbnails="false"
                    :showIndicators="true"
                    :showItemNavigators="true"
                    :changeItemOnIndicatorHover="true"
                    :showIndicatorsOnItem="true"
                    indicatorsPosition="bottom"
                    container-class="w-full"
                >
                    <!-- Template for displaying individual images -->
                    <template #item="slotProps">
                        <Image
                            :src="slotProps.item"
                            alt="Image"
                            imageClass="w-full h-[200px] object-contain"
                            class="w-full"
                            preview
                        />
                    </template>
                </Galleria>
            </div>
        </div>
    </div>
</template>