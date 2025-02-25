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

const props = defineProps({
    pendingConnection: Object,
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
    connection_id: props.pendingConnection.id,
    action: '',
    remarks: '',
});

const emit = defineEmits(['pendingConnectionActionCompleted']);

const submitForm = () => {
    form.action = dialogType.value;
    form.put(route('connection.pendingConnectionApproval'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans(`public.toast_${dialogType.value}_success`),
                life: 3000,
            });
            // Emit the custom event to parent
            emit('pendingConnectionActionCompleted');
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
                    <span class="text-surface-950 dark:text-white font-medium">{{ pendingConnection.user.name }}</span>
                    <span class="text-surface-500 text-sm">{{ pendingConnection.user.email }}</span>
                </div>
                <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                    $ {{ formatAmount(pendingConnection.capital_fund) }}
                </div>
            </div>

            <div class="flex flex-col gap-3 items-start w-full pt-4">
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.request_date') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ dayjs(pendingConnection.created_at).format('DD/MM/YYYY HH:mm:ss') }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.broker') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pendingConnection.broker.name }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.connection_number') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pendingConnection.connection_number }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.broker_login') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pendingConnection.broker_login }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.upline') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ pendingConnection.user.upline?.name || '-' }}
                        <span class="text-surface-500">
                            {{ pendingConnection.user.upline?.email }}
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