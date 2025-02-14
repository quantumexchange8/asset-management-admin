<script setup>
import Checkbox from 'primevue/checkbox';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { Link, useForm } from '@inertiajs/vue3';
import { IconMail, IconLock  } from '@tabler/icons-vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import AuthHeader from "@/Components/AuthHeader.vue";

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
    <GuestLayout title="login">
        <div class="flex flex-col gap-3 rounded-md p-5 dark:bg-surface-900 w-full max-w-[90vw] sm:max-w-md items-center justify-center">
            <AuthHeader
                :header="$t('public.login')"
                :caption="$t('public.login_caption')"
            />

            <form @submit.prevent="submit" class="w-full">
                <div class="flex flex-col gap-3 w-full self-stretch">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            :value="$t('public.email')"
                            for="email"
                        />
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
                                :placeholder="$t('public.enter_email')"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.password')" for="password"/>
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
                                placeholder="••••••••"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model="form.remember" :binary="true" />
                            <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ $t('public.remember_me') }}</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-gray-600 hover:text-primary dark:hover:text-primary-500 focus:outline-none dark:text-gray-400"
                        >
                            {{ $t('public.reset_password') }}
                        </Link>
                    </div>

                    <div class="flex flex-col gap-1 pt-5 items-center">
                        <Button
                            class="w-full text-center font-semibold dark:text-surface-ground text-white"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                        >
                            {{ $t('public.login') }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
