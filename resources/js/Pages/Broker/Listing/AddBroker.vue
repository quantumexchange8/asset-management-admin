<script setup>
import { useForm } from '@inertiajs/vue3';
import { IconExclamationCircle, IconLinkPlus, IconUserDollar, IconUsersPlus } from '@tabler/icons-vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import { useToast } from 'primevue/usetoast';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import { ref } from 'vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    locales: Array,
})

const visible = ref(false);

//file
const onSelectedBrokerImage = (event) => {
    form.broker_image = event.target.files[0];
};

const form = useForm({
    locales: props.locales,
    name: '',
    description_translation: {
        en: '',
    },
    note: '',
    url: '',
    broker_image: null,
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
                        <div class="card">
                            <Tabs :value="locales[0]">
                                <TabList>
                                    <Tab 
                                        v-for="locale in locales"
                                        :key="locale"
                                        :value="locale"
                                    >
                                    <div class="flex items-center gap-2">
                                        {{ $t(`public.${locale}`) }}
                                        
                                        <!-- Show error icon if there's a validation error -->
                                        <IconExclamationCircle
                                            v-if="form.errors[`description_translation.${locale}`]"
                                            size="20"
                                            stroke-width="1.5"
                                            class="text-red-500"
                                        />
                                    </div>
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
                <Button type="button" severity="secondary" @click="visible = false">Cancel</Button>
                <Button type="submit" :disabled="form.processing">Submit</Button>
            </div>
        </form>
    </Dialog>
</template>