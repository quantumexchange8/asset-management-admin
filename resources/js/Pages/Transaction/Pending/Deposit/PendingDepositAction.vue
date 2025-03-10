<script setup>
import { IconX, IconCheck } from '@tabler/icons-vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import Image from "primevue/image";
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import Galleria from 'primevue/galleria';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import {generalFormat} from "@/Composables/format.js";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    pending: Object,
});

const toast = useToast();
const visible = ref(false);
const {formatAmount} = generalFormat();

const openDialog = () => {
    visible.value = true;
}

const form = useForm({
    transaction_id: props.pending.id,
    action: '',
    remarks: '',
});

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

const submitForm = (action) => {
    form.action = action;
    form.put(route('transaction.pending.pendingDepositApproval'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans(`public.toast_${action}_transaction_success`),
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}

const closeDialog = () => {
    visible.value = false
}
</script>

<template>
    <div class="flex items-center self-stretch gap-2">
        <Button
            type="button"
            severity="secondary"
            size="small"
            :label="$t('public.view')"
            @click="openDialog"
        />
    </div>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.view_transaction')"
        class="dialog-xs md:dialog-md"
    >
        <div class="flex flex-col items-center gap-4 divide-y dark:divide-surface-700 self-stretch">
            <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full">
                <div class="flex flex-col items-start w-full">
                    <span class="text-surface-950 dark:text-white font-medium">{{ pending.user.name }}</span>
                    <span class="text-surface-500 text-sm">{{ pending.user.email }}</span>
                </div>
                <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                    $ {{ formatAmount(pending.amount) }}
                </div>
            </div>

            <div class="flex flex-col gap-3 items-start w-full pt-4">
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.request_date') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ dayjs(pending.created_at).format('DD/MM/YYYY HH:mm:ss') }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.transaction_number') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pending.transaction_number }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.wallet') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ $t(`public.${pending.to_wallet.type}`) }}
                    </div>
                </div>

                <div
                    v-if="pending.to_payment_platform === 'crypto'"
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
                            @click="copyToClipboard(pending.txn_hash)"
                        >
                            {{ pending.txn_hash }}
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="pending.transaction_type === 'deposit' && pending.media.length > 0" class="flex flex-col md:flex-row md:items-start gap-1 self-stretch pt-5">
                <div class="w-[140px] text-surface-500 text-xs font-medium">
                    {{ $t('public.payment_slip') }}
                </div>
                <div class="flex gap-2 col-span-2 items-center self-stretch w-full">
                    <Galleria
                        :value="pending.payment_slips"
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

            <div class="flex flex-col items-start gap-1 self-stretch pt-4">
                <InputLabel for="remarks">{{ $t('public.remarks') }}</InputLabel>
                <Textarea
                    id="remarks"
                    type="text"
                    class="flex flex-1 self-stretch"
                    v-model="form.remarks"
                    :placeholder="$t('public.remarks')"
                    :invalid="!!form.errors.remarks"
                    rows="5"
                    cols="30"
                    autofocus
                />
                <InputError :message="form.errors.remarks" />
            </div>

            <div class="flex gap-3 justify-between self-stretch pt-2 w-full">
                <Button
                    type="button"
                    severity="danger"
                    class="w-full"
                    :disabled="form.processing"
                    @click="submitForm('reject')"
                    :label="$t('public.reject_transaction')"
                />
                <Button
                    type="submit"
                    severity="success"
                    class="w-full"
                    :disabled="form.processing"
                    @click.prevent="submitForm('approve')"
                    :label="$t('public.approve_transaction')"
                />
            </div>
        </div>
    </Dialog>
</template>
