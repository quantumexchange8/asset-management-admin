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
const {formatAmount} = generalFormat();

const openDialog = () => {
    visible.value = true;
}

const form = useForm({
    connection_id: props.pendingConnection.id,
    action: '',
    remarks: '',
});

const submitForm = (action) => {
    form.action = action;
    form.put(route('connection.pendingConnectionApproval'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans(`public.toast_${action}_connection_success`),
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
        :header="$t('public.view_connection')"
        class="dialog-xs md:dialog-md"
    >
        <div class="flex flex-col items-center gap-4 divide-y dark:divide-surface-700 self-stretch">
            <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full">
                <div class="flex flex-col items-start w-full">
                    <span class="text-surface-950 dark:text-white font-medium">{{ pendingConnection.user.name }}</span>
                    <span class="text-surface-500 text-sm">{{ pendingConnection.user.email }}</span>
                </div>
                <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                    {{ formatAmount(pendingConnection.capital_fund) }}
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

            <div class="flex gap-3 justify-between self-stretch pt-2 w-full">
                <Button
                    type="button"
                    severity="danger"
                    class="w-full"
                    :disabled="form.processing"
                    @click="submitForm('reject')"
                    :label="$t('public.reject_connection')"
                />
                <Button
                    type="submit"
                    severity="success"
                    class="w-full"
                    :disabled="form.processing"
                    @click.prevent="submitForm('approve')"
                    :label="$t('public.approve_connection')"
                />
            </div>
        </div>
    </Dialog>
</template>