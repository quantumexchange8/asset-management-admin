<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import {
    IconUser,
    IconUserCog,
    IconMail,
    IconLock
} from "@tabler/icons-vue";
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import InputError from "@/Components/InputError.vue";
import Password from "primevue/password";
import Select from "primevue/select";
import Checkbox from "primevue/checkbox";
import {trans} from "laravel-vue-i18n";
import {useToast} from "primevue/usetoast";

defineProps({
    permissions: Object
})

const visible = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    permissions: null
});

const selectedRole = ref();
const roles = ref([
    'super_admin',
    'admin'
]);

const selectedPermissions = ref();
const toast = useToast();

const submitForm = () => {
    form.role = selectedRole.value
    form.permissions = selectedPermissions.value;

    form.post(route('settings.addAdmin'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans('public.toast_add_admin_success'),
                life: 3000,
            });
        }
    })
}

const closeDialog = () => {
    visible.value = false;
}
</script>

<template>
    <Button
        class="w-full md:w-auto flex gap-1"
        @click="visible = true"
        size="small"
    >
        <IconUser size="20" stroke-width="1.5" />
        {{ $t('public.add_admin') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.add_admin')"
        class="dialog-xs md:dialog-md"
    >
        <form class="w-full">
            <div class="flex flex-col gap-6 items-center self-stretch">
                <div class="flex flex-col gap-3 items-center self-stretch">
                    <span class="font-bold text-sm text-surface-950 dark:text-white w-full text-left">{{ $t('public.admin_credentials') }}</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full">
                        <div class="flex flex-col gap-1 items-start self-stretch">
                            <InputLabel
                                for="name"
                                :value="$t('public.name')"
                            />
                            <InputIconWrapper class="w-full">
                                <template #icon>
                                    <IconUser :size="20" stroke-width="1.5"/>
                                </template>

                                <InputText
                                    id="name"
                                    type="text"
                                    class="pl-10 block w-full"
                                    v-model="form.name"
                                    :placeholder="$t('public.enter_name')"
                                    autofocus
                                    :invalid="!!form.errors.name"
                                />
                            </InputIconWrapper>
                            <InputError :message="form.errors.name"/>
                        </div>

                        <div class="flex flex-col gap-1 items-start self-stretch">
                            <InputLabel
                                for="role"
                                :value="$t('public.role')"
                            />
                            <InputIconWrapper>
                                <template #icon>
                                    <IconUserCog :size="20" stroke-width="1.5"/>
                                </template>
                                <Select
                                    v-model="selectedRole"
                                    :options="roles"
                                    :placeholder="$t('public.select_role')"
                                    class="pl-7 block w-full"
                                    :invalid="!!form.errors.role"
                                >
                                    <template #value="slotProps">
                                        <div v-if="slotProps.value">
                                            <div>{{ $t(`public.${slotProps.value}`) }}</div>
                                        </div>
                                        <span v-else class="text-surface-500">{{ slotProps.placeholder }}</span>
                                    </template>
                                    <template #option="slotProps">
                                        {{ $t(`public.${slotProps.option}`) }}
                                    </template>
                                </Select>
                            </InputIconWrapper>
                            <InputError :message="form.errors.role"/>
                        </div>

                        <div class="flex flex-col gap-1 items-start self-stretch">
                            <InputLabel
                                for="email"
                                :value="$t('public.email')"
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
                                    :placeholder="$t('public.enter_email')"
                                    :invalid="!!form.errors.email"
                                />
                            </InputIconWrapper>
                            <InputError :message="form.errors.email"/>
                        </div>

                        <div class="flex flex-col gap-1 items-start self-stretch">
                            <InputLabel :value="$t('public.password')" for="password"/>
                            <InputIconWrapper>
                                <template #icon>
                                    <IconLock :size="20" stroke-width="1.5"/>
                                </template>
                                <Password v-model="form.password" :invalid="!!form.errors.password" class="block w-full" placeholder="••••••••" toggleMask />
                            </InputIconWrapper>
                            <InputError :message="form.errors.password"/>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="flex flex-col gap-3 items-center self-stretch">
                    <span class="font-bold text-sm text-surface-950 dark:text-white w-full text-left">{{ $t('public.manage_permissions') }}</span>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center self-stretch">
                        <div
                            v-for="(types, category) in permissions"
                            :key="category"
                            class="flex flex-col gap-3 self-stretch w-full"
                        >
                            <!-- Display Category -->
                            <div class="text-xs text-surface-400 dark:text-surface-500 uppercase">{{ $t(`public.${category}`) }}</div>

                            <div
                                v-for="(permissions, type) in types"
                                :key="type"
                                class="flex flex-col gap-1 self-stretch bg-white dark:bg-surface-800 p-3 rounded-md border border-surface-200 dark:border-surface-700 shadow-toast"
                            >
                                <!-- Display Type -->
                                <div class="text-sm font-semibold">{{ $t(`public.${type}`) }}</div>

                                <div class="flex flex-col gap-1">
                                    <div
                                        v-for="permission in permissions"
                                        :key="permission.name"
                                        class="flex gap-2 items-center"
                                    >
                                        <Checkbox
                                            v-model="selectedPermissions"
                                            :inputId="permission.name"
                                            :value="permission.id "
                                        />
                                        <label :for="permission.name" class="text-sm">{{ $t(`public.${permission.name}`)  }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                    <Button
                        type="button"
                        severity="secondary"
                        @click="closeDialog"
                        :label="$t('public.cancel')"
                    />
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        @click="submitForm"
                        :label="$t('public.confirm')"
                    />
                </div>
            </div>
        </form>
    </Dialog>
</template>
