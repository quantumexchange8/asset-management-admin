<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Password from 'primevue/password';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { IconLock } from '@tabler/icons-vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.$el.querySelector('input')?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.$el.querySelector('input')?.focus();
            }
        },
    });
};
</script>

<template>
    <form 
        @submit.prevent="updatePassword" 
        class="flex flex-col gap-5 self-stretch w-full"
    >
        <div class="flex flex-col gap-1 items-start self-stretch">
            <InputLabel for="current_password" :value="$t('public.current_password')" />

            <InputIconWrapper>
                <template #icon>
                    <IconLock :size="20" stroke-width="1.5"/>
                </template>
                <Password
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    class="block w-full"
                    autocomplete="current-password"
                    :invalid="!!form.errors.current_password"
                />
            </InputIconWrapper>

            <InputError
                :message="form.errors.current_password"
                class="mt-2"
            />
        </div>

        <div class="flex flex-col gap-1 items-start self-stretch">
            <InputLabel for="password" :value="$t('public.new_password')" />
            <InputIconWrapper>
                <template #icon>
                    <IconLock :size="20" stroke-width="1.5"/>
                </template>
                <Password
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    class="block w-full"
                    autocomplete="new-password"
                    :invalid="!!form.errors.password"
                />
            </InputIconWrapper>

            <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <div class="flex flex-col gap-1 items-start self-stretch">
            <InputLabel
                for="password_confirmation"
                :value="$t('public.confirm_password')"
            />
            <InputIconWrapper>
                <template #icon>
                    <IconLock :size="20" stroke-width="1.5"/>
                </template>
                <Password
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    class="block w-full"
                    autocomplete="new-password"
                    :invalid="!!form.errors.password_confirmation"
                />
            </InputIconWrapper>

            <InputError
                :message="form.errors.password_confirmation"
                class="mt-2"
            />
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
