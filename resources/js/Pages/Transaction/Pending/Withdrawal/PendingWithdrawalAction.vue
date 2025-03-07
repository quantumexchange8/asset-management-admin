<script setup>
import { IconX, IconCheck } from '@tabler/icons-vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { generalFormat } from '@/Composables/format';
import {trans} from "laravel-vue-i18n";
import Tag from 'primevue/tag';

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
    form.put(route('transaction.pending.pendingWithdrawalApproval'), {
        onSuccess: () => {
            visible.value = false;
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
                        {{ $t('public.to') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pending.to_payment_account_name }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        <Tag
                            :value="pending.to_payment_platform"
                            severity="info"
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
                            @click="copyToClipboard(pending.to_payment_account_no)"
                        >
                            {{ pending.to_payment_account_no }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.fee') }}
                    </div>
                    <div class="text-red-500 text-sm font-medium">
                        $ {{ formatAmount(pending.transaction_charges ?? 0) }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.receive') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        $ {{ formatAmount(pending.transaction_amount ?? 0) }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-start gap-1 self-stretch pt-4">
                <InputLabel for="remarks" :value="$t('public.remarks')" />
                <Textarea
                    id="remarks"
                    type="text"
                    v-model="form.remarks"
                    :invalid="!!form.errors.remarks"
                    :placeholder="$t('public.reject_remarks')"
                    class="block w-full"
                    autofocus
                    rows="5"
                    cols="30"
                />
                <InputError :message="form.errors.remarks" />
            </div>

            <div class="flex gap-3 justify-between self-stretch pt-5 w-full">
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
