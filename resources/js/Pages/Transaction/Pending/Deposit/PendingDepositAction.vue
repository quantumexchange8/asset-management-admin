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
import Galleria from 'primevue/galleria';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps({
    pending: Object, 
});

const toast = useToast();
const visible = ref(false);
const dialogType = ref(''); //reject or approve

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
            if(dialogType.value === 'approve'){
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
            size="sm"
            type="button"
            class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
            @click="openDialog('approve')"
        >
            <IconCheck :size="20" stroke-width="1.5" color="green"/>
        </Button>
    
        <Button
            class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
            size="sm"
            type="button"
            @click="openDialog('reject')"
        >
            <IconX :size="20" stroke-width="1.5" color="red"/>
        </Button>
    </div> 

    <Dialog
        v-model:visible="visible"
        modal
        :style="{ width: '35rem' }"
    >
    <template #header>
        <div class="flex items-center gap-4">
            <div class="text-xl font-bold">
                {{ dialogType.replace(/\b\w/g, (char) => char.toUpperCase()) }} Transaction
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
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">
                    Requested Date:
                </div>
                <div>
                    {{ dayjs(props.pending.approval_at).format('YYYY-MM-DD') }}
                    {{ dayjs(props.pending.approval_at).add(8, 'hour').format('hh:mm:ss A') }}
                </div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">Transaction Number:</div>
                <div>{{ props.pending.transaction_number }}</div>
            </div>
            <div class="flex justify-between text-sm">
                <div class=" text-gray-500">To:</div>
                <div>{{ props.pending.to_wallet.type.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()) }}</div>
            </div>
            <!-- <div class="flex justify-between text-sm">
                <div class=" text-gray-500">Upline:</div>
                <div>{{ props.pending.user.upline_id || '-' }}</div>
            </div> -->
        </div>

        <Divider />

        <!-- Payment Slip Section -->
        <div class="space-y-2">
            <div class="text-lg text-gray-500 mb-4">
                Payment Slip
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
                    <img 
                        :src="slotProps.item" 
                        alt="Image Preview" 
                        style="width: 100%; display: block;" 
                    />
                </template>
            </Galleria>
        </div>

        <div v-if="dialogType === 'reject'" class="space-y-2">
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