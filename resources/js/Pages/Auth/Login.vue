<script setup>
import Checkbox from 'primevue/checkbox';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Button from 'primevue/button';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import { IconMail, IconLock  } from '@tabler/icons-vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-6 py-2">
                <div class="space-y-2">
                    <InputLabel value="Email" for="email"/>
                    <InputIconWrapper>
                        <template #icon>
                            <IconMail :size="20" stroke-width="1.5"/>
                        </template>
                        <InputText
                            id="email"
                            type="email"
                            class="pl-10 block w-full"
                            v-model="form.email"
                            autofocus
                            :invalid="!!form.errors.email"
                            autocomplete="username"
                            placeholder="Email"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-2">
                    <InputLabel value="Password" for="password"/>
                    <InputIconWrapper>
                        <template #icon>
                            <IconLock :size="20" stroke-width="1.5"/>
                        </template>
                        <InputText
                            id="password"
                            type="password"
                            class="pl-10 block w-full"
                            v-model="form.password"
                            :invalid="!!form.errors.password"
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model="form.remember" :binary="true" />
                        <InputLabel class="ms-2 text-sm text-gray-700 dark:text-gray-300" value="Remember Me" />
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-gray-600 hover:text-primary dark:hover:text-primary-500 focus:outline-none dark:text-gray-400"
                    >
                        Reset Password
                    </Link>
                </div>

                <div class="text-center">
                    <Button
                        class="w-full text-center font-semibold dark:text-surface-ground text-white"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        type="submit"
                    >
                        Log In
                    </Button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
