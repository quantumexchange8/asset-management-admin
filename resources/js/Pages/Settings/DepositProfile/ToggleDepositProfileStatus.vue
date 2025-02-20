<script setup>
import ToggleSwitch from 'primevue/toggleswitch';
import {ref, watch} from "vue";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import {router} from "@inertiajs/vue3";
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    depositProfile: Object,
});

const toast = useToast();

const checked = ref(props.depositProfile.status === 'active')

watch(() => props.depositProfile.status, (newStatus) => {
    checked.value = newStatus === 'active';
});
const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        activate_item: {
            group: 'headless-success',
            header: trans('public.activate_deposit_profile'),
            text: trans('public.activate_deposit_profile_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('settings.updateDepositProfileStatus', props.depositProfile.id), {
                    method: 'put',
                    data: {
                        id: props.depositProfile.id,
                    },
                    onSuccess: () => {
                        toast.add({
                            severity: 'success',
                            summary: trans('public.success'),
                            detail: trans('public.toast_deposit_profile_active'),
                            life: 4000,
                        });
                    },
                    onError: (errors) => {
                        console.error(errors);
                    }
                })

                checked.value = !checked.value;
            }
        },
        deactivate_item: {
            group: 'headless-primary',
            header: trans('public.deactivate_deposit_profile'),
            text: trans('public.deactivate_deposit_profile_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('settings.updateDepositProfileStatus', props.depositProfile.id), {
                    method: 'put',
                    data: {
                        id: props.depositProfile.id,
                    },
                    onSuccess: () => {
                        toast.add({
                            severity: 'success',
                            summary: trans('public.success'),
                            detail: trans('public.toast_deposit_profile_inactive'),
                            life: 4000,
                        });
                    },
                    onError: (errors) => {
                        console.error(errors);
                    }
                })

                checked.value = !checked.value;
            }
        },
    };

    const { group, header, text, dynamicText, suffix, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
};

const handleStatus = () => {
    if (props.depositProfile.status === 'active') {
        requireConfirmation('deactivate_item')
    } else {
        requireConfirmation('activate_item')
    }
}
</script>

<template>
     <div class="flex items-center">
        <ToggleSwitch
            v-model="checked"
            readonly
            @click="handleStatus"
        />
    </div>
</template>