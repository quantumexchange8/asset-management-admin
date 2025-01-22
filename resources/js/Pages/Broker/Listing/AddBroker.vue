<script setup>
import { useForm } from '@inertiajs/vue3';
import { IconLinkPlus, IconUserDollar, IconUsersPlus } from '@tabler/icons-vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const visible = ref(false);

//file
const onSelectedBrokerImage = (event) => {
    form.broker_image = event.target.files[0];
};

const onSelectedQrImage = (event) => {
    form.broker_qr_image = event.target.files[0];
}

const form = useForm({
    name: '',
    description: '',
    note: '',
    url: '',
    broker_image: null,
    broker_qr_image: null,
});

const toast = useToast();

const submitForm = () => {
    form.post(route('broker.addNewBroker'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Broker added successfully!',
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};
</script>

<template>
    <Button class="w-full md:w-auto" @click="visible = true">
        <IconUsersPlus size="16" />
        <span class="pl-2">Add Broker</span>
    </Button>

    <Dialog v-model:visible="visible" modal class="dialog-xs md:dialog-md" style="width: 90%; max-width: 45rem;">
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    Add Broker
                </div>
            </div>
        </template>

        <form @submit.prevent="submitForm" class="flex flex-col gap-6 items-center self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="space-y-2">
                      <InputLabel for="name" value="Name"/>
                      <InputIconWrapper>
                            <template #icon>
                                <IconUserDollar :size="20" stroke-width="1.5"/> 
                            </template>

                            <InputText
                                id="name"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.name"
                                placeholder="Name"
                                :invalid="!!form.errors.name"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.name"/>
                    </div>

                    <div class="space-y-2">
                      <InputLabel for="url" value="URL"/>
                      <InputIconWrapper>
                            <template #icon>
                                <IconLinkPlus :size="20" stroke-width="1.5"/> 
                            </template>

                            <InputText
                                id="url"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.url"
                                placeholder="URL"
                                :invalid="!!form.errors.url"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.url"/>
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="description" value="Description"/>
                        <Textarea
                            id="description"
                            type="text"
                            class="block w-full"
                            v-model="form.description"
                            placeholder="Description"
                            :invalid="!!form.errors.description"
                            rows="5"
                            cols="30"
                        />
                        <InputError :message="form.errors.description"/>
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="note" value="Note"/>
                        <Textarea
                            id="note"
                            type="text"
                            class="block w-full"
                            v-model="form.note"
                            placeholder="Note"
                            :invalid="!!form.errors.note"
                            rows="5"
                            cols="30"
                        />
                        <InputError :message="form.errors.note"/>
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="broker_image" value="Broker Image"/>
                        <div class="flex justify-start">
                            <FileUpload
                                name="broker_image"
                                :multiple="false"
                                accept="image/*"
                                @input="onSelectedBrokerImage"
                                mode="basic"
                                chooseLabel="Choose Image"
                            />
                        </div>
                        <InputError :message="form.errors.broker_image" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="broker_qr_image" value="QR Code"/>
                        <div class="flex justify-start">
                            <FileUpload
                                name="broker_qr_image"
                                :multiple="false"
                                accept="image/*"
                                @input="onSelectedQrImage"
                                mode="basic"
                                chooseLabel="Choose Image"
                            />
                        </div>
                        <InputError :message="form.errors.broker_qr_image" />
                    </div>
                </div>
            </div>
            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button type="button" severity="secondary" @click="visible = false">Cancel</Button>
                <Button type="submit" :disabled="form.processing">Submit</Button>
            </div>
        </form>
    </Dialog>
</template>