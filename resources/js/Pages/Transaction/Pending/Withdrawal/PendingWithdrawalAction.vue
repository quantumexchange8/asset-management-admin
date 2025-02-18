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

const props = defineProps({
    pending: Object, 
});

const toast = useToast();
const visible = ref(false);
const dialogType = ref('');

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

</script>

<template>
    <div class="flex items-center self-stretch gap-x-2">
        <Button
            size="sm"
            type="button"
            class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
            @click="openDialog('approve_transaction')"
        >
            <IconCheck :size="20" stroke-width="1.5" color="green"/>
        </Button>
    
        <Button
            class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
            size="sm"
            type="button"
            @click="openDialog('reject_transaction')"
        >
            <IconX :size="20" stroke-width="1.5" color="red"/>
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
    
    <div class="grid gap-2 py-2">
        <!-- User Info Section (Name, Email, Amount) -->
        <div class="flex justify-between">
            <div class="text-sm">
                <div>{{ props.pending.user.name }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ props.pending.user.email }}
                </div>
            </div>
            <div class="text-lg">
                <div>
                    {{ props.pending.from_wallet.currency_symbol }}
                    {{ props.pending.amount }}
                </div>
            </div>
        </div>

        <Divider />

        <!-- Requested Date, Transaction Number, To, Upline Section -->
        <div class="flex flex-col gap-1 self-stretch">
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">
                    {{ $t('public.requested_at') }}:
                </div>
                <div>
                    {{ dayjs(props.pending.approval_at).format('YYYY-MM-DD') }}
                    {{ dayjs(props.pending.approval_at).add(8, 'hour').format('hh:mm:ss A') }}
                </div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.transaction_number') }}:</div>
                <div>{{ props.pending.transaction_number }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.wallet') }}:</div>
                <div>{{ $t(`public.${props.pending.from_wallet.type}`) }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.fee') }}:</div>
                <div>{{ props.pending.transaction_charges || '-' }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.receive') }}:</div>
                <div>$ {{ props.pending.transaction_amount || '-' }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.to') }}:</div>
                <div>{{ props.pending.user.name || '-' }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.upline') }}:</div>
                <div>{{ props.pending.user.upline?.name || '-' }} ({{ props.pending.user.upline?.email }})</div>
            </div>
        </div>

        <div v-if="dialogType === 'reject_transaction'"  class="flex flex-col gap-1 self-stretch">
            <Divider />
            <InputLabel for="remarks" value="Remarks" />
            <Textarea 
                id="remarks"
                type="text"
                v-model="form.remarks"
                :invalid="!!form.errors.remarks"
                placeholder="Reject Remarks"
                class="block w-full"
                autofocus
                rows="5"
                cols="30"
            />
            <InputError :message="form.errors.remarks" />
        </div>

        <div class="flex justify-center mt-3">
            <Button
                severity="secondary"
                class="text-center mr-3"
                @click="visible = false"
            >
                Cancel
            </Button>
            <Button
                class="text-center"
                :disabled="form.processing"
                @click.prevent="submitForm"
            >
                Submit
            </Button>
        </div>
    </div>
</Dialog>

</template>