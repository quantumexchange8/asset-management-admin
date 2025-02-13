<script setup>
import { IconX, IconCheck } from '@tabler/icons-vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import Galleria from 'primevue/galleria';
import { useForm } from '@inertiajs/vue3';
import Image from "primevue/image";

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
    user_id: props.pending.id,
    action: '',
    remarks: '',
});

const emit = defineEmits(['kycActionCompleted']);

const submitForm = () => {
    form.action = dialogType.value;
    form.put(route('member.kycPendingApproval'), {
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
            emit('kycActionCompleted');
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
    return props.pending.kyc_images.length > 0
        ? props.pending.kyc_images
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
        class="dialog-xs md:dialog-md"
    >
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    {{ dialogType.replace(/\b\w/g, (char) => char.toUpperCase()) }} KYC
                </div>
            </div>
        </template>

        <div class="grid gap-6 py-2 w-full">
            <div class="flex flex-col gap-1 items-start self-stretch w-full">
                <InputLabel for="ID/Passport" value="ID/Passport" />
                <Galleria
                    :value="imagesToDisplay"
                    :responsiveOptions="responsiveOptions"
                    :numVisible="5"
                    :circular="true"
                    :showItemNavigators="true"
                    :showThumbnails="false"
                    container-class="w-full"
                >
                    <!-- Template for displaying individual images -->
                    <template #item="slotProps">
                        <Image
                            :src="slotProps.item"
                            alt="Image"
                            imageClass="w-full h-[300px] object-cover"
                            class="w-full"
                            preview
                        />
                    </template>
                </Galleria>
            </div>

            <div v-if="dialogType === 'reject'" class="flex flex-col gap-1 items-start self-stretch">
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

            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
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
