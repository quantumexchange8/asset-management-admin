<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import { useToast } from 'primevue/usetoast';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { IconLinkPlus, IconUserDollar } from '@tabler/icons-vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    broker: Object,
    locales: Array,
});

const emit = defineEmits(['update:visible']);

//file
const onSelectedBrokerImage = (event) => {
    form.broker_image = event.target.files[0];
};

const form = useForm({
    locales: props.locales,
    id: props.broker.id,
    name: props.broker.name || '',
    description_translation: props.locales.reduce((acc, locale) => {
        acc[locale] = props.broker.description?.[locale] || ''; // Ensure it doesn't break if undefined
        return acc;
    }, {}),
    url: props.broker.url || '',
    broker_image: null,
}); 

const toast = useToast();

const submitForm = () => {
    form.processing = true;
    const formData = new FormData();
    formData.append('_method', 'put');
    formData.append('name', form.name);
    form.locales.forEach((locale) => {
        formData.append('locales[]', locale); // Append each locale as an array item
    });
     // Convert description_translation object to an array
     Object.keys(form.description_translation).forEach((locale) => {
        formData.append(`description_translation[${locale}]`, form.description_translation[locale]);
    });
    formData.append('url', form.url);
    if(form.broker_image){
        formData.append('broker_image', form.broker_image);
    }

    router.post(`/broker/detail/${form.id}/updateBrokerInfo`, formData, {
        onSuccess:() => {
            emit('update:visible', false);
            form.processing = false;
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Broker updated successfully!',
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
            form.errors = errors;
        }
    });
}
</script>

<template>
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
                    <div class="card">
                            <Tabs :value="props.locales[0]">
                                <TabList>
                                    <Tab 
                                        v-for="locale in props.locales"
                                        :key="locale"
                                        :value="locale"
                                    >
                                        {{ $t(`public.${locale}`) }}
                                    </Tab>
                                </TabList>
                                <TabPanels>
                                    <TabPanel
                                        v-for="locale in form.locales"
                                        :key="'input-' + locale"
                                        :value="locale"
                                    >
                                        <InputLabel 
                                            :for="'description' + locale"
                                            :value="`Description (${$t(`public.${locale}`)})`"
                                            class="mb-2"
                                        />
                                        <Textarea 
                                            :id="'description_' + locale"
                                            type="text"
                                            class="block w-full"
                                            v-model="form.description_translation[locale]"
                                            :placeholder="`Description (${$t(`public.${locale}`)})`"
                                            :invalid="!!form.errors[`description_translation.${locale}`]"
                                            rows="7"
                                            cols="30"
                                        />
                                        <InputError :message="form.errors[`description_translation.${locale}`]" />
                                    </TabPanel>
                                </TabPanels>
                            </Tabs>
                        </div>
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
            </div>
        </div>
        
        <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
            <Button type="button" severity="secondary" @click="$emit('update:visible', false)">Cancel</Button>
            <Button type="submit" :disabled="form.processing">Update</Button>
        </div>
    </form>
</template>