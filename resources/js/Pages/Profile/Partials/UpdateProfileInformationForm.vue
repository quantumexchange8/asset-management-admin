<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputText from 'primevue/inputtext';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import { ref } from "vue";
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import Checkbox from 'primevue/checkbox';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const date = ref()

const selectedCity = ref();
const selectedCities = ref();
const cities = ref([
    { name: 'New York', code: 'NY' },
    { name: 'Rome', code: 'RM' },
    { name: 'London', code: 'LDN' },
    { name: 'Istanbul', code: 'IST' },
    { name: 'Paris', code: 'PRS' }
]);

const pizza = ref();
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <InputText
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                    :invalid="form.errors.name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <InputText
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    disabled
                    :invalid="form.errors.email"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="test" value="Input Number" />

                <InputNumber
                    class="mt-1 block w-full"
                    showButtons 
                    :min="0"
                    :invalid="form.errors.name"
                />

            </div>

            <div>
                <InputLabel for="date" value="Date Picker" />

                <DatePicker
                    class="mt-1 block w-full"
                    :invalid="form.errors.name"
                    showButtonBar 
                    v-model="date"
                />

            </div>

            <div>
                <InputLabel for="selects" value="Select" />
                <Select 
                    v-model="selectedCity" 
                    :invalid="form.errors.name"
                    :options="cities" 
                    optionLabel="name" 
                    filter
                    placeholder="Select a City" 
                    class="mt-1 block w-full" 
                    showClear
                />
            </div>

            <div>
                <InputLabel for="multiselect" value="Multi-Select" />
                <MultiSelect 
                    v-model="selectedCities" 
                    :invalid="form.errors.name"
                    :options="cities" 
                    optionLabel="name" 
                    filter 
                    placeholder="Select Cities"
                    :maxSelectedLabels="3" 
                    class="mt-1 block w-full"  
                />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox 
                v-model="pizza" 
                inputId="ingredient1" 
                name="pizza" 
                value="Cheese" 
                />
                <InputLabel for="box" value="Checkbox" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
