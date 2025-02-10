<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Select from 'primevue/select';
import InputError from '@/Components/InputError.vue';
import { IconTransferIn, IconUsers, IconX, IconUpload, IconPhotoPlus, IconFileCheck, IconTableImport } from '@tabler/icons-vue';
import { onMounted, ref } from 'vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

const visible = ref(false);

const openDialog = () => {
    visible.value = true;
    getBrokers();
}

//get brokers
const selectedBrokers = ref();
const brokers = ref([]);
const loadingBrokers = ref(false);


const getBrokers = async () => {
    loadingBrokers.value = true;

    try {
        const response = await axios.get('/get_brokers');
        brokers.value = response.data.brokers;
    } catch (error) {
        console.error('Error fetching selectedBrokers:', error);
    } finally {
        loadingBrokers.value = false;
    }
}

//file
const file = ref(null);

const isDragging = ref(false);

const dragOver = () => {
    isDragging.value = true;
};

const dragLeave = () => {
    isDragging.value = false;
};

const handleDrop = (event) => {
    isDragging.value = false;
    const droppedFiles = event.dataTransfer.files;
    if (droppedFiles.length > 0) {
        validateFile(droppedFiles[0]);
    }
};

const handleFileSelect = (event) => {
    const selectedFile = event.target.files[0];
    validateFile(selectedFile);
};

const validateFile = (fileInput) => {
    const validFormats = ["application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "text/csv"];
    if (validFormats.includes(fileInput.type)) {
        file.value = fileInput;
        form.commission_file = file.value;
    } else {
        toast.add({
            severity: 'error',
            summary: trans('public.error'),
            detail: trans('public.toast_import_error'),
            life: 5000,
        });
    }
};

//useform
const form = useForm({
    broker: '',
    commission_file: null,
});

//submit form
const toast = useToast();

const submit = () => {
    form.broker = selectedBrokers.value;
    form.post(route('report.importCommissions'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans('public.toast_import_success'),
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);

             // Filter out the 'broker' field from the errors
            const errorMessages = Object.entries(errors)
                .filter(([key]) => key !== 'broker')  // Exclude 'broker' field
                .map(([, value]) => value); // Extract only the error messages
          
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

const isDownloading = ref(false);

//download excel tempalte
const downloadTemplate = async() => {
    isDownloading.value = true;
    try {
        const response = await axios.get(route('report.download_import_example'), {
            responseType: 'blob', // Ensure the response is a file
        });

        const blob = new Blob([response.data], { type: response.headers['content-type'] });
        const url = window.URL.createObjectURL(blob);

        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = 'commission_import_example.xlsx'; // Set the filename
        document.body.appendChild(anchor);
        anchor.click();
        document.body.removeChild(anchor);

        // Revoke the URL to free up memory
        window.URL.revokeObjectURL(url);
        toast.add({
            severity: 'success',
            summary: trans('public.success'),
            detail: trans('public.toast_download_example_success'),
            life: 3000,
        });
    } catch (error) {
        console.error('Error download import example:', error);
    } finally {
        isDownloading.value = false;
    }
};
</script>

<template>

    <!-- Import button -->
    <Button 
        class="w-full md:w-auto flex justify-center items-center"
        @click="openDialog"
    >
        <span class="pr-1"> {{ $t('public.import') }}</span>
        <IconTransferIn size="16" stroke-width="1.5" />
    </Button>
 
    <!-- Import Modal -->
    <Dialog 
        v-model:visible="visible" 
        modal 
        class="dialog-xs md:dialog-md"
     >
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
            <div class="flex flex-col gap-5 self-stretch py-1">
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputIconWrapper class="w-full">
                        <template #icon>
                            <IconUsers :size="20" stroke-width="1.5"/>
                        </template>
                        <Select
                            v-model="selectedBrokers"
                            :options="brokers"
                            :loading="loadingBrokers"
                            optionLabel="name"
                            placeholder="Select Broker"
                            class="pl-7 block w-full"
                            :invalid="!!form.errors.broker"
                            filter
                            showClear
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.name }}</div>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                        </Select>
                    </InputIconWrapper>
                    <InputError :message="form.errors.broker"/>
                </div>

                <div class="flex flex-col gap-1 self-stretch">
                    <div
                        :class="[
                            'flex flex-col gap-3 items-center self-stretch p-10 rounded-md border-2 border-dashed transition-colors duration-150',
                            {
                                'border-blue-500 dark:text-blue-400 bg-blue-200/50 dark:bg-blue-800/10': isDragging,
                                'border-surface-300 dark:border-surface-600': !isDragging,
                            }
                        ]"
                        @dragover.prevent="dragOver"
                        @dragleave.prevent="dragLeave"
                        @drop.prevent="handleDrop"
                    >
                        <div
                            v-if="file"
                            class="rounded-full w-14 h-14 shrink-0 grow-0 border border-green-300 dark:border-green-600 flex items-center justify-center text-green-500 dark:text-green-400"
                        >
                            <IconFileCheck size="28" stroke-width="1.5" />
                        </div>

                        <div
                            v-else
                            :class="[
                                'rounded-full w-14 h-14 shrink-0 grow-0 border border-surface-300 dark:border-surface-600 flex items-center justify-center',
                                {
                                    'text-blue-500 dark:text-blue-400': isDragging,
                                    'text-surface-600 dark:text-surface-400': !isDragging,
                                }
                            ]"
                        >
                            <IconTableImport size="28" stroke-width="1.5" />
                        </div>

                        <div
                            v-if="file"
                            class="flex flex-col gap-1 items-center self-stretch"
                        >
                            <span class="text-sm font-medium text-green-500">{{ $t('public.ready_to_import') }}</span>
                            <span class="text-sm text-surface-600 dark:text-surface-400">{{ file.name }}</span>
                            <label
                                for="fileInput"
                                class="text-xs text-blue-500 cursor-pointer underline select-none hover:text-blue-600"
                            >
                                {{ $t('public.replace_file') }}
                            </label>
                        </div>

                        <div v-else class="text-sm">
                            {{ $t('public.drag_and_drop') }} <label for="fileInput" class="text-blue-500 cursor-pointer underline select-none hover:text-blue-600">{{ $t('public.choose_file') }}</label>.
                        </div>
                        <input type="file" id="fileInput" class="hidden" @change="handleFileSelect" accept=".xlsx, .xls, .csv" />
                        <InputError :message="form.errors.commission_file"/>
                    </div>

                    <div class="flex flex-col md:flex-row gap-1 md:justify-between items-center">
                        <div class="text-xs text-surface-500 dark:text-surface-400">
                            {{ $t('public.supported_format') }}: .xlsx, .xls, .csv
                        </div>
                        <div class="text-xs text-surface-500 dark:text-surface-400">
                            {{ $t('public.maximum_size') }}: 25MB
                        </div>
                    </div>

                    <div class="bg-surface-100 dark:bg-surface-800 p-3 rounded-md flex flex-col md:flex-row gap-3 md:gap-10 items-center self-stretch mt-3">
                        <div class="flex flex-col gap-1 items-start self-stretch w-full">
                            <div class="flex gap-2 items-center">
                                <img src="/img/logo/excel-logomark.svg" alt="excel-logomark" class="w-5" />
                                <span class="text-sm font-semibold">{{ $t('public.table_example') }}</span>
                            </div>
                            <div class="text-xs text-surface-500 dark:text-surface-400">
                                {{ $t('public.table_example_caption') }}
                            </div>
                        </div>
                        <Button
                            type="button"
                            severity="contrast"
                            :label="$t('public.download')"
                            size="small"
                            class="w-full md:min-w-32 md:w-fit"
                            @click="downloadTemplate"
                            :disabled="isDownloading"
                        />
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button
                        severity="secondary"
                        class="text-center mr-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="closeModal()"
                    >
                        {{ $t('public.cancel') }}
                    </Button>
                    <Button
                        class="text-center"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        type="submit"
                    >
                        {{ $t('public.import') }}
                    </Button>
                </div>
            </div>
        </form>
    </Dialog>
</template>