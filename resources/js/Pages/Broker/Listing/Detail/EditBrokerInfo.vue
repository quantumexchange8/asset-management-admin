<script setup>
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import {IconHomeDollar, IconLinkPlus} from '@tabler/icons-vue';
import { useForm } from '@inertiajs/vue3';
import Image from "primevue/image";
import Checkbox from "primevue/checkbox";
import {ref} from "vue";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    broker: Object,
    locales: Array,
});

const emit = defineEmits(['update:visible']);

const brokerActions = ref([
    'register',
    'deposit'
]);

const selectedActions = ref(
    props.broker.actions
        ? props.broker.actions
            .filter(action => brokerActions.value.includes(action.broker_action))
            .map(action => action.broker_action)
        : []
);

const defaultActionUrls = brokerActions.value.reduce((acc, action) => {
    acc[action] = props.broker.actions?.find(a => a.broker_action === action)?.action_url || '';
    return acc;
}, {});

const form = useForm({
    locales: props.locales,
    id: props.broker.id,
    name: props.broker.name || '',
    description_translation: props.locales.reduce((acc, locale) => {
        acc[locale] = props.broker.description?.[locale] || '';
        return acc;
    }, {}),
    url: props.broker.url || '',
    broker_image: null,
    action_url: { ...defaultActionUrls },
});

const toast = useToast();

const submitForm = () => {
    form.action_url = Object.keys(defaultActionUrls)
        .filter(action => selectedActions.value.includes(action)) // Keep only selected actions
        .reduce((acc, action) => {
            acc[action] = defaultActionUrls[action];
            return acc;
        }, {});

    form.post(route('broker.detail.updateBrokerInfo'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans('public.toast_update_broker_success'),
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}

const closeDialog = () => {
    emit('update:visible', false);
}

const selectedLogo = ref(null);

const handleLogoUpload = (event) => {
    const brokerLogoInput = event.target;
    const file = brokerLogoInput.files[0];

    if (file) {
        // Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedLogo.value = reader.result;
        };
        reader.readAsDataURL(file);
        form.broker_image = event.target.files[0];
    } else {
        selectedLogo.value = null;
    }
};
</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col gap-6 items-center self-stretch">
        <div class="flex flex-col gap-3 items-center self-stretch">
            <!-- Upload Image -->
            <div class="flex flex-col gap-3 items-center self-stretch">
                <div class="w-20 h-20 grow-0 shrink-0 rounded-full border border-surface-200 dark:border-surface-800 flex items-center justify-center z-20">
                    <Image
                        v-if="selectedLogo"
                        :src="selectedLogo"
                        alt="Image"
                        imageClass="w-16 h-16 object-cover rounded-full"
                        class="rounded-full"
                        preview
                    />
                    <Image
                        v-else
                        :src="broker.media[0].original_url"
                        alt="Image"
                        imageClass="w-16 h-16 object-cover rounded-full"
                        class="rounded-full"
                        preview
                    />
                </div>
                <Button
                    type="button"
                    severity="info"
                    size="small"
                    class="w-full md:w-fit"
                    :label="$t('public.browse')"
                    @click="$refs.brokerLogoInput.click()"
                />
                <input
                    ref="brokerLogoInput"
                    id="broker_logo"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleLogoUpload"
                />
                <InputError :message="form.errors.broker_image"/>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <InputLabel
                        for="name"
                        :value="$t('public.name')"
                    />
                    <InputIconWrapper class="w-full">
                        <template #icon>
                            <IconHomeDollar :size="20" stroke-width="1.5"/>
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
                        for="url"
                        :value="$t('public.url')"
                    />
                    <InputIconWrapper class="w-full">
                        <template #icon>
                            <IconLinkPlus :size="20" stroke-width="1.5"/>
                        </template>

                        <InputText
                            id="url"
                            type="text"
                            class="pl-10 block w-full"
                            v-model="form.url"
                            :placeholder="$t('public.enter_url')"
                            :invalid="!!form.errors.url"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.url"/>
                </div>

                <!-- Description -->
                <div class="flex flex-col gap-3 items-start self-stretch md:col-span-2 ">
                    <span class="font-bold text-gray-950 dark:text-white w-full text-left">{{ $t('public.descriptions') }}</span>
                    <div class="flex flex-col md:flex-row gap-5 self-stretch w-full">
                        <!-- Checkbox for selecting locales -->
                        <div class="flex flex-col gap-1 items-start self-stretch min-w-40">
                            <InputLabel :value="$t('public.languages')" />
                            <div class="flex flex-row md:flex-col gap-1">
                                <div
                                    v-for="locale in locales"
                                    :key="locale"
                                    class="flex items-center"
                                >
                                    <Checkbox
                                        v-model="form.locales"
                                        :inputId="locale"
                                        :value="locale"
                                        :disabled="locale === 'en'"
                                    />
                                    <label :for="locale" class="ml-2 text-sm">{{ $t(`public.${locale}`) }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamically generated input fields for each selected locale -->
                        <div class="flex flex-col gap-1 items-start self-stretch w-full">
                            <div class="grid md:grid-cols-2 gap-3 w-full">
                                <div
                                    v-for="locale in form.locales"
                                    :key="'input-' + locale"
                                    class="flex flex-col items-start gap-1 self-stretch"
                                >
                                    <InputLabel
                                        :for="'name_' + locale"
                                        :value="`${$t('public.description')} (${$t(`public.${locale}`)})`"
                                        :invalid="!!form.errors[`description_translation.${locale}`]"
                                    />
                                    <Textarea
                                        :id="'description_' + locale"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.description_translation[locale]"
                                        :placeholder="`${$t('public.description')} (${$t(`public.${locale}`)})`"
                                        :invalid="!!form.errors[`description_translation.${locale}`]"
                                        rows="7"
                                        cols="30"
                                    />
                                    <InputError :message="form.errors[`description_translation.${locale}`]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3 items-start self-stretch md:col-span-2 ">
                    <span class="font-bold text-gray-950 dark:text-white w-full text-left">{{ $t('public.authorization') }}</span>
                    <div class="flex flex-col md:flex-row gap-5 self-stretch w-full">
                        <!-- Checkbox for selecting locales -->
                        <div class="flex flex-col gap-1 items-start self-stretch min-w-40">
                            <InputLabel :value="$t('public.actions')" />
                            <div class="flex flex-row md:flex-col gap-1">
                                <div
                                    v-for="action in brokerActions"
                                    :key="action"
                                    class="flex items-center"
                                >
                                    <Checkbox
                                        v-model="selectedActions"
                                        :inputId="action"
                                        :value="action"
                                    />
                                    <label :for="action" class="ml-2 text-sm">{{ $t(`public.${action}`) }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamically generated input fields for each selected actions -->
                        <div class="flex flex-col gap-1 items-start self-stretch w-full">
                            <div class="grid md:grid-cols-2 gap-3 w-full">
                                <div
                                    v-for="action in selectedActions"
                                    :key="'input-' + action"
                                    class="flex flex-col items-start gap-1 self-stretch"
                                >
                                    <InputLabel
                                        :for="'action_' + action"
                                        :value="`${$t('public.url')} (${$t(`public.${action}`)})`"
                                        :invalid="!!form.errors[`action_url.${action}`]"
                                    />
                                    <InputText
                                        :id="'action_' + action"
                                        type="text"
                                        class="block w-full"
                                        v-model="defaultActionUrls[action]"
                                        :placeholder="$t('public.enter_url')"
                                        :invalid="!!form.errors[`action_url.${action}`]"
                                    />
                                    <InputError :message="form.errors[`action_url.${action}`]" />
                                </div>
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
                :label="$t('public.submit')"
            />
        </div>
    </form>
</template>
