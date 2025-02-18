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
import {useToast} from "primevue/usetoast";
import {onMounted, watch} from "vue";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
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

const toast = useToast();

onMounted(() => {
    if (props.status) {
        toast.add({
            severity: 'success',
            summary: trans('public.success'),
            detail: trans('public.toast_success_reset_password'),
            life: 3000,
        });
    }
});
</script>

<template>
    <GuestLayout title="login">
        <div class="flex flex-col gap-3 rounded-md p-5 dark:bg-surface-900 w-full max-w-[90vw] sm:max-w-md items-center justify-center">
            <div class="flex flex-col items-center self-stretch w-full transition-colors duration-200">
                <span class="text-lg md:text-xl font-semibold text-surface-950 dark:text-white">{{ $t('public.admin_panel') }}</span>
                <span class="font-medium text-xs md:text-base text-surface-500">{{ $t('public.log_in') }}</span>
            </div>

            <form @submit.prevent="submit" class="w-full">
                <div class="flex flex-col gap-3 w-full self-stretch">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            :value="$t('public.email')"
                            for="email"
                        />
                        <InputIconWrapper class="w-full">
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
                        <InputIconWrapper class="w-full">
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
                            <span class="ms-2 text-sm text-surface-700 dark:text-surface-300">{{ $t('public.remember_me') }}</span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 pt-5 items-center">
                        <Button
                            class="w-full text-center font-semibold dark:text-surface-ground text-white"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                        >
                            {{ $t('public.log_in') }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
