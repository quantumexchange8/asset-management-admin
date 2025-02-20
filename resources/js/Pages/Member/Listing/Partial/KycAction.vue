<script setup>
import { IconX, IconCheck } from '@tabler/icons-vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import Galleria from 'primevue/galleria';
import {useForm} from '@inertiajs/vue3';
import Image from "primevue/image";
import {trans} from "laravel-vue-i18n";
import { useLangObserver } from '@/Composables/localeObserver';

const props = defineProps({
    pending: Object,
});

const toast = useToast();
const visible = ref(false);
const dialogType = ref('');
const {locale} = useLangObserver();

const openDialog = async (action) => {
    visible.value = true;
    dialogType.value = action;
}

const form = useForm({
    user_id: props.pending.id,
    action: '',
    remarks: '',
});

const submitForm = () => {
    form.action = dialogType.value;
    form.post(route('member.kycPendingApproval'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans(`public.toast_${dialogType.value}_kyc_success`),
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
            severity="success"
            text
            rounded
            aria-label="approve"
            size="small"
            class="!p-2"
            @click="openDialog('approve')"
        >
            <IconCheck :size="20" stroke-width="1.5" />
        </Button>
        <Button
            type="button"
            severity="danger"
            text
            rounded
            aria-label="reject"
            size="small"
            class="!p-2"
            @click="openDialog('reject')"
        >
            <IconX :size="20" stroke-width="1.5" />
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
                    {{ $t(`public.${dialogType}_kyc`) }}
                </div>
            </div>
        </template>

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

            <div class="flex flex-col gap-1 items-start self-stretch w-full">
                <InputLabel for="kyc">
                    {{ $t('public.proof_of_identity') }}:
                </InputLabel>
                <Galleria
                    :value="pending.kyc_images"
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
                            imageClass="w-full h-[300px] object-contain"
                            class="w-full"
                            preview
                        />
                    </template>
                </Galleria>
            </div>

            <div v-if="dialogType === 'reject'" class="flex flex-col gap-1 items-start self-stretch">
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

            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button
                    type="button"
                    severity="secondary"
                    class="w-full md:w-fit"
                    @click="closeDialog"
                    :label="$t('public.cancel')"
                />
                <Button
                    type="submit"
                    class="w-full md:w-fit"
                    :disabled="form.processing"
                    @click.prevent="submitForm"
                    :label="$t('public.confirm')"
                />
            </div>
        </div>
    </Dialog>
</template>
