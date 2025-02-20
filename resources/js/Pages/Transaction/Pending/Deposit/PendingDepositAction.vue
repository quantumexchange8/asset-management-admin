<script setup>
import { IconX, IconCheck } from '@tabler/icons-vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Divider from 'primevue/divider';
import Image from "primevue/image";
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import Galleria from 'primevue/galleria';
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

const emit = defineEmits(['pendingDepositActionCompleted']);

const submitForm = () => {
    form.action = dialogType.value;
    form.put(route('transaction.pending.pendingDepositApproval'), {
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
            emit('pendingDepositActionCompleted');
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}

const responsiveOptions = ref([
    {
        breakpoint: '991px',
        numVisible: 4
    },
    {
        breakpoint: '767px',
        numVisible: 3
    },
    {
        breakpoint: '575px',
        numVisible: 1
    }
]);

// Use a placeholder image if kycImages is empty
const imagesToDisplay = computed(() => {
    return props.pending.pending_deposit_pay_slip.length > 0
        ? props.pending.pending_deposit_pay_slip
        : ['/image-not-found.jpg']; // Replace with the path to your placeholder image
});
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
                    {{ props.pending.to_wallet.currency_symbol }}
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
                <div class=" text-gray-500">{{ $t('public.to') }}:</div>
                <div>{{ $t(`public.${props.pending.to_wallet.type}`) }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">{{ $t('public.upline') }}:</div>
                <div>{{ props.pending.user.upline?.name || '-' }}</div>
            </div>
        </div>

        <Divider />

        <!-- Payment Slip Section -->
        <div class="flex flex-col gap-1 self-stretch">
            <div class="text-lg text-gray-500 mb-4">
                {{ $t('public.payment_slip') }}
            </div>
            <Galleria 
                :value="imagesToDisplay" 
                :responsiveOptions="responsiveOptions" 
                :numVisible="5" 
                :circular="true"
                containerStyle="max-width: 640px" 
                :showItemNavigators="true" 
                :showThumbnails="false"
            >
                <!-- Template for displaying individual images -->
                <template #item="slotProps">
                    <Image 
                        :src="slotProps.item" 
                        alt="Image" 
                        style="width: 100%; display: block;" 
                        preview
                    />
                </template>
            </Galleria>
        </div>

        <div v-if="dialogType === 'reject_transaction'" class="flex flex-col gap-1 self-stretch">
            <Divider />
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

        <div class="flex justify-center mt-3">
            <Button
                severity="secondary"
                class="text-center mr-3"
                @click="visible = false"
            >
                {{ $t('public.cancel') }}
            </Button>
            <Button
                class="text-center"
                :disabled="form.processing"
                @click.prevent="submitForm"
            >
                {{ $t('public.submit') }}
            </Button>
        </div>
    </div>
</Dialog>

</template>