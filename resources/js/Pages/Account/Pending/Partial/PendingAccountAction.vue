<script setup>
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Galleria from 'primevue/galleria';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import {useForm} from '@inertiajs/vue3';
import Image from "primevue/image";
import {trans} from "laravel-vue-i18n";
import { useLangObserver } from '@/Composables/localeObserver';
import dayjs from 'dayjs';

const props = defineProps({
    accounts: Object,
});

const toast = useToast();
const visible = ref(false);

const openDialog = () => {
    visible.value = true;
}

const form = useForm({
    account_id: props.accounts.id,
    action: '',
    remarks: '',
});

const submitForm = (action) => {
    form.action = action;
    form.post(route('broker_accounts.pendingAccountApproval'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans(`public.toast_${action}_account_success`),
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
        :header="$t('public.view_account')"
        class="dialog-xs md:dialog-md"
    >
       
        <div class="flex flex-col items-center gap-4 divide-y dark:divide-surface-700 self-stretch">
            <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full">
                <div class="flex flex-col items-start w-full">
                    <span class="text-surface-950 dark:text-white font-medium">{{ accounts.user.name }}</span>
                    <span class="text-surface-500 text-sm">{{ accounts.user.email }}</span>
                </div>
                <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                    {{ accounts.broker_login }}
                </div>
            </div>

            <div class="flex flex-col gap-3 items-start w-full pt-4">
                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.request_date') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ dayjs(accounts.created_at).subtract(8, 'hour').format('DD/MM/YYYY HH:mm:ss') }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.broker') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ accounts.broker.name }}
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                    <div class="w-[140px] text-surface-500 text-xs font-medium">
                        {{ $t('public.master_password') }}
                    </div>
                    <div class="text-surface-950 dark:text-white text-sm font-medium">
                        {{ accounts.decrypt_master_password }}
                    </div>
                </div>
            </div>

            <div v-if="accounts.media.length > 0" class="flex flex-col md:flex-row md:items-start gap-1 self-stretch pt-5">
                <div class="w-[140px] text-gray-500 text-xs font-medium">
                    {{ $t('public.account_proof') }}
                </div>
                <div class="flex gap-2 col-span-2 items-center self-stretch w-full">
                    <Galleria
                        :value="accounts.broker_account_image"
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
                <InputLabel for="remarks" :value="$t('public.remarks')" />
                <Textarea
                    id="remarks"
                    type="text"
                    v-model="form.remarks"
                    :invalid="!!form.errors.remarks"
                    :placeholder="$t('public.remarks')"
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
                    :label="$t('public.reject_account')"
                />
                <Button
                    type="submit"
                    severity="success"
                    class="w-full"
                    :disabled="form.processing"
                    @click.prevent="submitForm('approve')"
                    :label="$t('public.approve_account')"
                />
            </div>
        </div>
    </Dialog>
</template>