<script setup>
import { IconCopy } from '@tabler/icons-vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import Image from 'primevue/image';
import dayjs from 'dayjs';
import { useLangObserver } from '@/Composables/localeObserver';
import EditBrokerInfo from './EditBrokerInfo.vue';

const toast = useToast();
const { locale } = useLangObserver();

const props = defineProps({
    broker: Object,
    broker_image: Array,
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Link successfully copied!',
            life: 3000,
        });
    }).catch((err) => {
        console.error('Failed to copy text: ', err);
    });
};
</script>

<template>
    <div class="flex flex-col lg:flex-row gap-5 w-full">
        <!-- Left Card -->
        <div class="flex-[1.5] lg:w-3/5">
            <Card class="w-full relative">
                <template #content>
                    <div class="text-lg font-semibold mb-1">{{ broker.name }}</div>
                    <div class="text-sm text-gray-500 mb-4">
                        Last Updated: {{ dayjs(broker.updated_at).format('YYYY-MM-DD') }} {{ dayjs(broker.updated_at).add(8, 'hour').format('hh:mm:ss A') }}
                    </div>

                    <div class="mb-4">
                        <div class="font-semibold">URL</div>
                        <div class="flex items-center space-x-2 w-2/3">
                          
                            <div class="flex-1 w-1/2 rounded p-2 bg-gray-100 dark:bg-surface-800 truncate">
                                {{ broker.url }}
                            </div>
                           
                            <Button 
                                @click="copyToClipboard(broker.url)"
                            >
                                <IconCopy size="20" stroke-width="1.5" />
                            </Button>
                        </div>
                    </div>

                    <!-- Description and Note Side by Side with More Gap -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                        <div>
                            <div class="font-semibold">Description</div>
                            <div class="text-sm text-gray-600 dark:text-surface-400 text-justify">
                                {{ broker.description[locale] }}
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

       <!-- Right Side with Two Stacked Cards -->
       <div class="flex flex-col flex-1 lg:w-2/5 gap-5">
            <Card class="w-full">
                <template #content>
                    <div class="text-lg font-semibold mb-2">Broker Image</div>
                    <div class="flex justify-center">
                        <Image 
                            :src="props.broker_image" 
                            alt="Broker Image"
                            width="320"
                        />
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
