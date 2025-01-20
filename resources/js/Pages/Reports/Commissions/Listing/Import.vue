<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Select from 'primevue/select';
import InputError from '@/Components/InputError.vue';
import { IconTransferIn, IconUsers, IconX, IconUpload, IconPhotoPlus, IconFileCheck } from '@tabler/icons-vue';
import { ref } from 'vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

const visible = ref(false);

//file
const onSelectedFiles = (event) => {
    form.commission_file = event.target.files[0]; 
};

//useform
const form = useForm({
    broker: '',
    commission_file: null,
});

//submit form
const toast = useToast();

const submit = () => {
    form.post(route('report.importCommissions'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Import Successfully!',
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);

            const errorMessages = Object.values(errors);
          
            let str = '';

            for(let i = 0; i < errorMessages.length; i++){
                str += errorMessages[i] + "<br />";
            }
            
            form.errors.commission_file = str;
        }
    });
};

const closeModal = () => {
    visible.value = false;
    form.reset();
    form.errors.broker = '';
    form.errors.commission_file = '';
}

//download excel tempalte
const downloadTemplate = () => {
    // Directly link to the Laravel route for downloading
    window.location.href = '/report/download-template';
};
</script>

<template>

    <!-- Import button -->
    <Button class="w-full md:w-auto flex justify-center items-center" @click="visible = true">
        <span class="pr-1">Import</span>
        <IconTransferIn size="16" stroke-width="1.5" />
    </Button>
 
    <!-- Import Modal -->
    <Dialog v-model:visible="visible" modal :style="{ width: '30rem' }">
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    Import Commissions
                </div>
                <Link 
                    href="#"
                    @click.prevent="downloadTemplate"
                    class="text-sm text-primary font-semibold underline"
                >
                    Download Template
                </Link>
            </div>
        </template>
        <form @submit.prevent="submit">
            <div class="grid gap-6 py-2">
                <div class="space-y-2">
                    <InputIconWrapper>
                        <template #icon>
                            <IconUsers :size="20" stroke-width="1.5"/>
                        </template>
                        <Select
                            placeholder="Select Broker"
                            class="pl-7 block w-full"
                            :invalid="!!form.errors.broker"
                            filter
                        >

                        </Select>
                    </InputIconWrapper>
                    <InputError :message="form.errors.broker"/>
                </div>

                <div class="space-y-2">
                    <FileUpload
                        name="commission_file"
                        :multiple="false"
                        accept=".xlsx, .xls, .csv" 
                        @input="onSelectedFiles"
                    >
                        <template #header="{ chooseCallback, clearCallback, files }">
                            <div class="flex flex-wrap justify-between items-center flex-1 gap-4">
                                <div class="flex gap-2">
                                    <Button 
                                        @click="chooseCallback()" 
                                        rounded 
                                        outlined
                                        severity="secondary"
                                        class="!p-0 flex items-center justify-center h-10 w-10"
                                    >
                                        <IconPhotoPlus :size="20" stroke-width="1.5"/>
                                    </Button>
    
                                    <Button 
                                        @click="clearCallback()"  
                                        rounded 
                                        outlined 
                                        severity="danger"
                                        class="!p-0 flex items-center justify-center h-10 w-10"
                                        :disabled="!files || files.length === 0"
                                    >
                                        <IconX :size="20" stroke-width="1.5"/>
                                    </Button>
                                </div>
                            </div>
                            <InputError :message="form.errors.commission_file" />
                        </template>
                        <template #content="{ files }">
                            <div v-if="files.length > 0">
                                <div class="flex flex-col items-center justify-center text-center h-52">
                                    <IconFileCheck :size="30" stroke-width="1" />
                                    <span>File uploaded!</span>
                                </div>
                            </div>
                        </template>
                        <template #empty>
                            <div class="flex items-center justify-center flex-col gap-3 h-52">
                                <div class="flex items-center justify-center p-3 text-surface-400 dark:text-surface-600 rounded-full border border-surface-400 dark:border-surface-600">
                                    <IconUpload size="24" stroke-width="1.5" />
                                </div>
                                <p class="text-sm">Drag and drop files to here to upload.</p>
                            </div>
                        </template>
                    </FileUpload>
                </div>
            </div>

            <div class="flex justify-center mt-3">
                <Button
                    severity="secondary"
                    class="text-center mr-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="closeModal()"
                >
                    Cancel
                </Button>
                <Button
                    class="text-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    type="submit"
                >
                    Submit
                </Button>
            </div>
        </form>
    </Dialog>
</template>