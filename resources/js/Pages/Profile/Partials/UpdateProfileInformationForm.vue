<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Avatar from "primevue/avatar";
import {ref} from "vue";
import {generalFormat} from "@/Composables/format.js";
import Button from "primevue/button";
import Select from "primevue/select";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {IconFlag, IconLabel, IconPhone, IconUser, IconUserScan} from "@tabler/icons-vue";
import {useToast} from "primevue/usetoast";
import {trans} from "laravel-vue-i18n";
import {useLangObserver} from "@/Composables/localeObserver.js";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const {formatNameLabel} = generalFormat();
const {locale} = useLangObserver();

const form = useForm({
    name: user.name,
    username: user.username,
    email: user.email,
    country_id: '',
    dial_code: '',
    phone: user.phone,
    phone_number: '', 
    profile_photo: null,
    profile_action: '',
    identity_number: user.identity_number,
});

const removeProfilePhoto = () => {
    selectedProfilePhoto.value = null;
    form.profile_photo = null;
    form.profile_action = 'remove';
};

const selectedProfilePhoto = ref(usePage().props.auth.profile_photo);
const handleUploadProfilePhoto = (event) => {
    const profilePhotoInput = event.target;
    const file = profilePhotoInput.files[0];

    if (file) {
        // Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedProfilePhoto.value = reader.result;
        };
        reader.readAsDataURL(file);
        form.profile_photo = event.target.files[0];
    } else {
        selectedProfilePhoto.value = null;
    }
};


const selectedCountry = ref();
const selectedPhoneCode = ref();
const countries = ref([]);
const loadingCountries = ref(false);

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/get_countries');
        countries.value = response.data.countries;

        selectedCountry.value = countries.value.find(
            (country) => country.id === user.country_id
        ) || null;

        selectedPhoneCode.value = countries.value.find(
            (country) => country.phone_code === user.dial_code
        ) || null;
    } catch (error) {
        console.error('Error fetching selectedCountry:', error);
    } finally {
        loadingCountries.value = false;
    }
}

getCountries();
const toast = useToast();

const submitForm = () => {
    form.country_id = selectedCountry.value;
    form.dial_code = selectedPhoneCode.value.phone_code;
    form.phone_number = selectedPhoneCode.value.phone_code + form.phone;

    form.post(route('profile.update'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans('public.toast_update_profile_success_message'),
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
    <form
        @submit.prevent="submitForm"
        class="flex flex-col gap-5 self-stretch w-full"
    >
        <div class="flex flex-col gap-2 items-center self-stretch py-3">
            <Avatar
                v-if="selectedProfilePhoto"
                :image="selectedProfilePhoto"
                size="xlarge"
                shape="circle"
            />
            <Avatar
                v-else
                :label="formatNameLabel(user.name)"
                size="xlarge"
                shape="circle"
            />

            <div class="flex items-center gap-4">
                <input
                    ref="profilePhotoInput"
                    id="kyc_verification"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleUploadProfilePhoto"
                />
                <Button
                    type="button"
                    severity="info"
                    size="small"
                    :disabled="form.processing"
                    @click.prevent="$refs.profilePhotoInput.click()"
                >
                    {{ $t('public.change') }}
                </Button>
                <Button
                    type="button"
                    severity="danger"
                    size="small"
                    outlined
                    :disabled="!selectedProfilePhoto || form.processing"
                    @click.prevent="removeProfilePhoto"
                >
                    {{ $t('public.remove') }}
                </Button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 w-full gap-3">
            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="name"
                    :value="$t('public.name')"
                />
    
                <InputIconWrapper>
                    <template #icon>
                        <IconUser :size="20" stroke-width="1.5"/>
                    </template>

                    <InputText
                        id="name"
                        type="text"
                        class="pl-10 block w-full"
                        v-model="form.name"
                        :placeholder="$t('public.enter_name')"
                        autocomplete="name"
                        :invalid="!!form.errors.name"
                    />
                </InputIconWrapper>
    
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="username"
                    :value="$t('public.username')"
                />

                <InputIconWrapper>
                    <template #icon>
                        <IconLabel :size="20" stroke-width="1.5"/>
                    </template>
                    <InputText
                        id="username"
                        type="text"
                         class="pl-10 block w-full"
                        v-model="form.username"
                        :placeholder="$t('public.enter_username')"
                        :invalid="!!form.errors.username"
                    />
                </InputIconWrapper>

                <InputError class="mt-2" :message="form.errors.username" />
            </div>
    
            <div class="flex flex-col gap-1 md:col-span-2 items-start self-stretch">
                <InputLabel for="email" :value="$t('public.email')" />
    
                <InputText
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    :invalid="!!form.errors.email"
                    disabled
                    autocomplete="username"
                />
    
                <InputError class="mt-2" :message="form.errors.email" />
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
                        :invalid="!!form.errors.country_id"
                        filter
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                {{ JSON.parse(slotProps.value.translations)[locale] || slotProps.value.name }}
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
                <InputError :message="form.errors.country_id" />
            </div>

            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel :value="$t('public.phone_number')" for="phone"/>
                <div class="flex gap-2 items-center self-stretch relative">
                    <Select
                        v-model="selectedPhoneCode"
                        :options="countries"
                        :loading="loadingCountries"
                        optionLabel="phone_code"
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
                       
                        <InputText
                            id="phone"
                            type="text"
                            class="pl-10 block w-full"
                            v-model="form.phone"
                            placeholder="1234567"
                            :invalid="!!form.errors.phone"
                        />
                    </InputIconWrapper>
                </div>
                <InputError :message="form.errors.phone" />
                <InputError :message="form.errors.dial_code" />
            </div>

            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="identity_number"
                    :value="$t('public.identity_number')"
                />

                <InputIconWrapper>
                    <template #icon>
                        <IconUserScan :size="20" stroke-width="1.5"/>
                    </template>
                    <InputText
                        id="identity_number"
                        type="text"
                        class="pl-10 block w-full"
                        v-model="form.identity_number"
                        :placeholder="$t('public.enter_identity_number')"
                        :invalid="!!form.errors.identity_number"
                    />
                </InputIconWrapper>
                <InputError class="mt-2" :message="form.errors.identity_number" />
            </div>
        </div>

        <div class="flex items-center justify-end">
            <Button
                type="submit"
                :label="$t('public.save')"
                :disabled="form.processing"
            />
        </div>
    </form>
</template>
