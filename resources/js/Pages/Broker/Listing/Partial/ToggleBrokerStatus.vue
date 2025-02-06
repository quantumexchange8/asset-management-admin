<script setup>
import ToggleSwitch from 'primevue/toggleswitch';
import {ref, watch} from "vue";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import {router} from "@inertiajs/vue3";
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    broker: Object,
})

const toast = useToast();

const checked = ref(props.broker.status === 'active')

watch(() => props.broker.status, (newStatus) => {
    checked.value = newStatus === 'active';
});
const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        activate_item: {
            group: 'headless-success',
            header: trans('public.activate_broker'),
            text: trans('public.activate_broker_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('broker.updateBrokerStatus', props.broker.id), {
                    method: 'put',
                    data: {
                        id: props.broker.id,
                    },
                    onSuccess: () => {
                        toast.add({
                            severity: 'success',
                            summary: 'Success',
                            detail: 'Broker activated successfully!',
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
            header: trans('public.deactivate_broker'),
            text: trans('public.deactivate_broker_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('broker.updateBrokerStatus', props.broker.id), {
                    method: 'put',
                    data: {
                        id: props.broker.id,
                    },
                    onSuccess: () => {
                        toast.add({
                            severity: 'success',
                            summary: 'Success',
                            detail: 'Broker deactivated successfully!',
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
    if (props.broker.status === 'active') {
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
