<script setup>
import { IconEdit } from '@tabler/icons-vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from 'primevue/select';
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    memberInfo: Object,
})

const visible = ref(false);
const selectedCountry = ref();
const selectedPhoneCode = ref();
const countries = ref([]);

//loading
const loadingCountries = ref(false);

const getCountries = async () => {
    loadingCountries.value = true;
    try{
        const response = await axios.get('/get_countries');
        countries.value = response.data.countries;
    } catch(error){
        console.error('Error fetching selectedCountry:', error);
    } finally {
        loadingCountries.value = false;
    }
}

onMounted(() => {
    getCountries();
});

const form = useForm({
    user_id: props.memberInfo.id,
    name: props.memberInfo.name || '',
    username: props.memberInfo.username || '',
    country: null,
    dial_code: null,
    phone: props.memberInfo.phone || '',
    phone_number: null,
});

watch(countries, () => { //select tag
    selectedCountry.value = countries.value.find(country => country.id === props.memberInfo.country_id);
    selectedPhoneCode.value = countries.value.find(country => country.phone_code === props.memberInfo.dial_code);
});

const toast = useToast();

const submitForm = () => {
    form.country = selectedCountry.value;
    form.dial_code = selectedPhoneCode.value;

    if(selectedPhoneCode.value){
        form.phone_number = selectedPhoneCode.value.phone_code + form.phone;
    }

    form.put(route('member.detail.updateMemberProfile',{id_number: form.user_id}),{
        onSuccess:() => {
            visible.value = false;
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Profile updated successfully!',
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}
</script>

<template>
    <Button 
        @click="visible = true" 
        type="button"
        >
        <IconEdit size="20" stroke-width="1.5"/>
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        header="Contact Information"
        class="dialog-xs md:dialog-md"
    >
        <form @submit.prevent="submitForm" class="flex flex-col gap-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">

                <!-- Name -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="name"
                        value="Name"
                    />
                    <InputText
                        id="name"
                        type="text"
                        class="block w-full"
                        v-model="form.name"
                        placeholder="Name as per NRIC or passport"
                        :invalid="form.errors.name"
                        autofocus
                    />  
                    <InputError :message="form.errors.name"/>
                </div>

                <!-- Username -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="username"
                        value="Username"
                    />
                    <InputText
                        id="usrname"
                        type="text"
                        class="block w-full"
                        v-model="form.username"
                        placeholder="Enter Username"
                        :invalid="form.errors.username"
                        autofocus
                    />  
                    <InputError :message="form.errors.username"/>
                </div>

                <!-- Country -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel for="country" value="Country"/>
                    <Select
                        v-model="selectedCountry"
                        :options="countries"
                        :loading="loadingCountries"
                        optionLabel="name"
                        placeholder="Select Country"
                        class="w-full"
                        :invalid="form.errors.country"
                        filter
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                <div>{{ slotProps.value.name }}</div>
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center gap-1">
                                <div>{{ slotProps.option.emoji }}</div>
                                <div class="max-w-[200px] truncate">{{ slotProps.option.name }}</div>
                            </div>
                        </template>
                    </Select>
                    <InputError :message="form.errors.country" />
                </div>

                <!-- Phone -->
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel for="phone" value="Phone Number"/>
                    <div class="flex gap-2 items-center self-stretch relative">
                        <Select
                            v-model="selectedPhoneCode"
                            :options="countries"
                            :loading="loadingCountries"
                            optionLabel="name"
                            placeholder="Phone Code"
                            class="w-[100px]"
                            :invalid="form.errors.dial_code"
                            filter
                            :filterFields="['name', 'iso2', 'phone_code']"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.phone_code }}</div>
                                </div>
                                <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span> 
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1">
                                    <div>{{ slotProps.option.emoji }}</div>
                                    <div>{{ slotProps.option.iso2 }}</div>
                                    <div class="max-w-[200px] truncate text-gray-500">({{ slotProps.option.phone_code }})</div>
                                </div>
                            </template>
                        </Select>

                        <InputText 
                            id="phone"
                            type="text"
                            class="block w-full"
                            v-model="form.phone"
                            placeholder="Phone Number"
                            :invalid="form.errors.phone"
                        />
                    </div>
                    <InputError :message="form.errors.phone"/>
                    <InputError :message="form.errors.dial_code"/>
                    <InputError :message="form.errors.phone_number"/> 
                </div>
            </div>
            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button type="button" label="Cancel" severity="secondary" @click="visible = false"></Button>
                <Button type="submit" label="Save" :disabled="form.processing"></Button>
            </div>
        </form> 
    </Dialog>
</template>