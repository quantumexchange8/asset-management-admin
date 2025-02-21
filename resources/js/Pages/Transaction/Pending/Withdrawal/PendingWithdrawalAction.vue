<script setup>
import { IconX, IconCheck } from '@tabler/icons-vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Divider from 'primevue/divider';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    pending: Object,
});

const toast = useToast();
const visible = ref(false);
const dialogType = ref('');
const {formatAmount} = generalFormat();

const openDialog = async (action) => {
    visible.value = true;
    dialogType.value = action;
}

const form = useForm({
    transaction_id: props.pending.id,
    action: '',
    remarks: '',
});

const emit = defineEmits(['pendingWithdrawalActionCompleted']);

const submitForm = () => {
    form.action = dialogType.value;
    form.put(route('transaction.pending.pendingWithdrawalApproval'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            if(dialogType.value === 'approve_transaction'){
                toast.add({
                    severity: 'success',
                    summary: 'Approved',
                    detail: 'Approved successfully!',
                    life: 3000,
                });
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Rejected',
                    detail: 'Rejected successfully!',
                    life: 3000,
                });
            }
            // Emit the custom event to parent
            emit('pendingWithdrawalActionCompleted');
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
    <div class="flex items-center self-stretch gap-x-2">
        <Button
            type="button"
            severity="success"
            text
            rounded
            aria-label="approve"
            size="small"
            class="!p-2"
            @click="openDialog('approve_transaction')"
        >
            <IconCheck :size="20" stroke-width="1.5"/>
        </Button>

        <Button
            type="button"
            severity="danger"
            text
            rounded
            aria-label="reject"
            size="small"
            class="!p-2"
            @click="openDialog('reject_transaction')"
        >
            <IconX :size="20" stroke-width="1.5"/>
        </Button>
    </div>

    <Dialog
        v-model:visible="visible"
        modal
        class="dialog-xs md:dialog-md"
    >
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    {{ $t(`public.${dialogType}`) }}
                </div>
            </div>
        </template>

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
                        {{ dayjs(pending.approval_at).format('DD/MM/YYYY HH:mm:ss') }}
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
                        {{ pending.from_wallet?.type ? $t(`public.${pending.from_wallet.type}`) : '-' }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.to') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pending.to_payment_account_name }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.fee') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
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

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.upline') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ props.pending.user.upline?.name || '-' }}
                        <span class="text-surface-500">
                            {{ props.pending.user.upline?.email }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-if="dialogType === 'reject_transaction'"  class="flex flex-col items-start gap-1 self-stretch pt-4">
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

            <div class="pt-5 flex gap-3 justify-end items-center self-stretch w-full">
                <Button
                    type="button"
                    :label="$t('public.cancel')"
                    severity="secondary"
                    variant="outlined"
                    class="px-3 w-full md:w-auto"
                    :disabled="form.processing"
                    @click="closeDialog"
                />

                <Button
                    type="submit"
                    class="px-3 w-full md:w-auto"
                    :label="$t('public.confirm')"
                    :disabled="form.processing"
                    @click.prevent="submitForm"
                />
            </div>
        </div>
    </Dialog>
</template>
