<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Password from 'primevue/password';
import Select from 'primevue/select';
import { onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { IconUserPlus } from '@tabler/icons-vue';

const visible = ref(false);
const selectedUpline = ref();
const selectedCountry = ref();
const selectedPhoneCode = ref();
const users = ref([]);
const countries = ref([]);

//loading
const loadingCountries = ref(false);
const loadingUsers = ref(false);

const getUsers = async () => {
    loadingUsers.value = true;
    try {
        const response = await axios.get('/get_users');
        users.value = response.data.users;
    } catch (error) {
        console.error('Error fetching selectedUpline:', error);
    } finally {
        loadingUsers.value = false;
    }
}

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/get_countries');
        countries.value = response.data.countries;
    } catch (error) {
        console.error('Error fetching selectedCountry:', error);
    } finally {
        loadingCountries.value = false;
    }
}

onMounted(() => {
    getUsers();
    getCountries();
});

const form = useForm({
    name: '',
    email: '',
    username: '',
    country: '',
    upline: '', //user name
    dial_code: '', //dialcode is used to bind data from selectedPhoneCode (not in form)
    phone: '',
    phone_number: '', // to join dialcode with phone (not in form)
    kyc_verification: '',
    password: '',
    password_confirmation: '',
})

const toast = useToast();

const submitForm = () => {
    form.country = selectedCountry.value;
    form.dial_code = selectedPhoneCode.value;

    if(selectedUpline.value){ //if upline data is present in select tag, bind with selectedUpline
        form.upline = selectedUpline.value;
    }

    if(selectedPhoneCode.value){
        form.phone_number = selectedPhoneCode.value.phone_code + form.phone;
    }

    form.post(route('member.addNewMember'), {
        onSuccess: () => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Member added successfully!',
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    })
}

</script>

<template>
    <Button class="w-full md:w-auto" @click="visible = true">
        <IconUserPlus size="16" />
        <span class="pl-2">Add Member</span>
    </Button>

    <!-- Modal -->
    <Dialog v-model:visible="visible" modal :header="'New Member'" class="dialog-xs md:dialog-md">

        <form @submit.prevent="submitForm" class="flex flex-col gap-6 items-center self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ 'Basics' }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    
                    <div class="flex flex-col gap-1 items-start self-stretch">
                      <InputLabel for="name" value="Name"/>
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

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="email" value="Email"/>
                        <InputText
                                id="email"
                                type="text"
                                class="block w-full"
                                v-model="form.email"
                                placeholder="example@example.com"
                                :invalid="form.errors.email"
                        />

                      <InputError :message="form.errors.email"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="username" value="Username"/>
                        <InputText
                                id="username"
                                type="text"
                                class="block w-full"
                                v-model="form.username"
                                placeholder="Enter Username"
                                :invalid="form.errors.username"
                        />

                        <InputError :message="form.errors.username"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="upline" value="Upline"/>
                        <Select
                            v-model="selectedUpline"
                            :options="users"
                            :loading="loadingUsers"
                            optionLabel="name"
                            placeholder="Select Upline"
                            class="w-full"
                            :invalid="form.errors.upline"
                            filter
                        >
                            <template #option="slotProps">
                                <div class="flex items-center gap-1 max-w-[220px] truncate">
                                    <span>{{ slotProps.option.name }}</span>
                                    <span class="text-xs text-gray-500">@{{ slotProps.option.username }}</span>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.upline" />    
                    </div>

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
                      <InputError :message="form.errors.phone" />
                      <InputError :message="form.errors.dial_code" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ 'Credentials' }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <!-- Password Field -->
                    <div class="flex flex-col gap-1 items-start w-full">
                      <InputLabel for="password" value="Password"/>
                      <Password 
                        v-model="form.password"
                        toggleMask
                        :invalid="form.errors.password"
                        class="w-full"
                      />
                      <InputError :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="flex flex-col gap-1 items-start w-full">
                        <InputLabel for="password_confirmation" value="Confirm Password"/>
                        <Password 
                            v-model="form.password_confirmation"
                            toggleMask
                            :invalid="form.errors.password"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button type="button" label="Cancel" severity="secondary" @click="visible = false"></Button>
                <Button type="submit" label="Save" :disabled="form.processing"></Button>
            </div>
        </form>
    </Dialog>
</template>