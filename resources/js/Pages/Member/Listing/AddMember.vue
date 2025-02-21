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
import { IconFlag, IconLabel, IconLock, IconMail, IconPhone, IconUser, IconUserPlus, IconUsersPlus } from '@tabler/icons-vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { useLangObserver } from '@/Composables/localeObserver';
import {trans} from "laravel-vue-i18n";

const visible = ref(false);
const selectedUpline = ref();
const selectedCountry = ref();
const selectedPhoneCode = ref();
const users = ref([]);
const countries = ref([]);
const {locale} = useLangObserver();

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

const openDialog = () => {
    visible.value = true;
    getUsers();
    getCountries();
}

const form = useForm({
    name: '',
    email: '',
    username: '',
    country: '',
    upline: '', //user name
    dial_code: '', //dialcode is used to bind data from selectedPhoneCode (not in form)
    phone: '',
    phone_number: '', // to join dialcode with phone (not in form)
    identity_number: '',
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
            closeDialog();
            form.reset();

            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans('public.toast_add_member_success'),
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    })
}

const closeDialog = () => {
    visible.value = false;
    selectedUpline.value = null;
    selectedCountry.value = null;
    selectedPhoneCode.value = null;
}
</script>

<template>
    <Button
        class="w-full md:w-auto"
        @click="openDialog"
    >
        <IconUserPlus size="16" />
        <span class="pl-2">{{ $t('public.add_member') }}</span>
    </Button>

    <!-- Modal -->
    <Dialog v-model:visible="visible" modal :header="$t('public.add_member')" class="dialog-xs md:dialog-md">
        <form @submit.prevent="submitForm" class="flex flex-col gap-6 items-center self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.name')" for="name" />
                        <InputIconWrapper>
                            <template #icon>
                                <IconUser :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText id="name" type="text" class="pl-10 block w-full" v-model="form.name" :placeholder="$t('public.enter_name')" :invalid="!!form.errors.name" autofocus/>
                        </InputIconWrapper>
                        <InputError :message="form.errors.name"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.email')" for="email"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconMail :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText id="email" type="text" class="pl-10 block w-full" v-model="form.email" :placeholder="$t('public.enter_email')" :invalid="!!form.errors.email"/>
                        </InputIconWrapper>
                        <InputError :message="form.errors.email"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.username')" for="username"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconLabel :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText id="username" type="text" class="pl-10 block w-full" v-model="form.username" :placeholder="$t('public.enter_username')" :invalid="!!form.errors.username"/>
                        </InputIconWrapper>
                        <InputError :message="form.errors.username"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.upline')" for="upline"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconUsersPlus :size="20" stroke-width="1.5"/>
                            </template>
                            <Select v-model="selectedUpline" :options="users" :loading="loadingUsers" optionLabel="name" :placeholder="$t('public.select_upline')" class="pl-7 block w-full" :invalid="!!form.errors.upline" filter>
                                <template #option="slotProps">
                                    <div class="flex items-center gap-1 max-w-[220px] truncate">
                                        <span>{{ slotProps.option.name }}</span>
                                        <span class="text-xs text-gray-500">@{{ slotProps.option.username }}</span>
                                    </div>
                                </template>
                            </Select>
                        </InputIconWrapper>
                        <InputError :message="form.errors.upline"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.country')" for="country"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconFlag :size="20" stroke-width="1.5"/>
                            </template>
                            <Select
                                v-model="selectedCountry"
                                :options="countries"
                                :loading="loadingCountries"
                                optionLabel="name"
                                :placeholder="$t('public.select_country')"
                                class="pl-7 block w-full"
                                :invalid="!!form.errors.country"
                                :filter-fields="['name', 'iso2']"
                                filter
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center">
                                        <div class="leading-tight">{{ JSON.parse(slotProps.value.translations)[locale] || slotProps.value.name }}</div>
                                    </div>
                                    <span v-else class="text-surface-400 dark:text-surface-500">{{ slotProps.placeholder }}</span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex items-center gap-1">
                                        <img
                                            v-if="slotProps.option.iso2"
                                            :src="`https://flagcdn.com/w40/${slotProps.option.iso2.toLowerCase()}.png`"
                                            :alt="slotProps.option.iso2"
                                            width="18"
                                            height="12"
                                        />
                                        <div class="max-w-[200px] truncate">{{ JSON.parse(slotProps.option.translations)[locale] || slotProps.option.name }}</div>
                                    </div>
                                </template>
                            </Select>
                        </InputIconWrapper>
                        <InputError :message="form.errors.country"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.phone_number')" for="phone"/>
                        <div class="flex gap-2 items-center self-stretch relative">
                            <Select
                                v-model="selectedPhoneCode"
                                :options="countries"
                                :loading="loadingCountries"
                                optionLabel="name"
                                placeholder="60"
                                class="w-[100px]"
                                :invalid="!!form.errors.dial_code"
                                filter
                                :filterFields="['name', 'iso2', 'phone_code']"
                            >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.phone_code }}</div>
                                </div>
                                <span v-else class="text-surface-400 dark:text-surface-500">{{ slotProps.placeholder }}</span>
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1">
                                    <img
                                        v-if="slotProps.option.iso2"
                                        :src="`https://flagcdn.com/w40/${slotProps.option.iso2.toLowerCase()}.png`"
                                        :alt="slotProps.option.iso2"
                                        width="18"
                                        height="12"
                                    />
                                    <div>{{ slotProps.option.phone_code }}</div>
                                    <div class="max-w-[200px] truncate text-gray-500">({{ slotProps.option.iso2 }})</div>
                                </div>
                            </template>
                            </Select>
                            <InputIconWrapper>
                                <template #icon>
                                    <IconPhone :size="20" stroke-width="1.5"/>
                                </template>
                                <InputText id="phone" type="text" class="block w-full pl-10" v-model="form.phone" placeholder="1234567" :invalid="!!form.errors.phone"/>
                            </InputIconWrapper>
                        </div>
                        <InputError :message="form.errors.phone"/>
                        <InputError :message="form.errors.dial_code"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.identity_number')" for="identity_number" />
                        <InputIconWrapper>
                            <template #icon>
                                <IconUser :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText id="identity_number" type="text" class="pl-10 block w-full" v-model="form.identity_number" :placeholder="$t('public.enter_identity_number')" :invalid="!!form.errors.identity_number" autofocus/>
                        </InputIconWrapper>
                        <InputError :message="form.errors.identity_number"/>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.credentials') }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.password')" for="password"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconLock :size="20" stroke-width="1.5"/>
                            </template>
                            <Password v-model="form.password" :invalid="!!form.errors.password" class="block w-full" placeholder="••••••••" toggleMask autofocus/>
                        </InputIconWrapper>
                        <InputError :message="form.errors.password"/>
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.confirm_password')" for="password_confirmation"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconLock :size="20" stroke-width="1.5"/>
                            </template>
                            <Password v-model="form.password_confirmation" :invalid="!!form.errors.password" class="block w-full" placeholder="••••••••" toggleMask/>
                        </InputIconWrapper>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button type="button" :label="$t('public.cancel')" severity="secondary" @click="closeDialog"/>
                <Button type="submit" :label="$t('public.save')" :disabled="form.processing"/>
            </div>
        </form>
    </Dialog>
</template>
