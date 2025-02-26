<script setup>
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import {useForm} from '@inertiajs/vue3';
import Image from "primevue/image";
import {trans} from "laravel-vue-i18n";
import { useLangObserver } from '@/Composables/localeObserver';

const props = defineProps({
    pending: Object,
});

const toast = useToast();
const visible = ref(false);
const {locale} = useLangObserver();

const openDialog = () => {
    visible.value = true;
}

const form = useForm({
    user_id: props.pending.id,
    action: '',
    remarks: '',
});

const submitForm = (action) => {
    form.action = action;
    form.post(route('member.kycPendingApproval'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans(`public.toast_${action}_kyc_success`),
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
        :header="$t('public.view_kyc')"
        class="dialog-xs md:dialog-lg"
    >
        <div class="flex flex-col items-center gap-4">
            <div class="flex flex-row items-center gap-3 self-stretch w-full">
                <div class="flex flex-col items-start w-full">
                    <span class="text-surface-950 dark:text-white font-medium">{{ pending.name }}</span>
                    <span class="text-surface-500 text-sm">{{ pending.email }}</span>
                </div>
                <div class="w-full text-surface-950 dark:text-white flex flex-col justify-end text-right">
                    <span>{{ pending.identity_number }}</span>
                    <span class="text-sm text-surface-500">{{ JSON.parse(pending.country.translations)[locale] || pending.country.name }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel for="front_identity">{{ $t('public.front_identity' )}}</InputLabel>
                    <div
                        class="flex flex-col gap-3 items-center self-stretch px-5 py-8 rounded-md border-2 border-dashed transition-colors duration-150 bg-surface-50 dark:bg-surface-ground border-surface-300 dark:border-surface-600"
                    >
                        <Image
                            role="presentation"
                            alt="front_identity_image"
                            :src="pending.front_identity"
                            preview
                            imageClass="w-full object-contain h-44"
                        />
                    </div>
                </div>
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel for="front_identity">{{ $t('public.back_identity' )}}</InputLabel>
                    <div
                        class="flex flex-col gap-3 items-center self-stretch px-5 py-8 rounded-md border-2 border-dashed transition-colors duration-150 bg-surface-50 dark:bg-surface-ground border-surface-300 dark:border-surface-600"
                    >
                        <Image
                            role="presentation"
                            alt="back_identity_image"
                            :src="pending.back_identity"
                            preview
                            imageClass="w-full object-contain h-44"
                        />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1 items-start self-stretch">
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
                    :label="$t('public.reject_kyc')"
                />
                <Button
                    type="submit"
                    severity="success"
                    class="w-full"
                    :disabled="form.processing"
                    @click.prevent="submitForm('approve')"
                    :label="$t('public.approve_kyc')"
                />
            </div>
        </div>
    </Dialog>
</template>
