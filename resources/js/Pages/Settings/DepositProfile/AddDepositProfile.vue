<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import InputError from '@/Components/InputError.vue';
import { IconBrandTether, IconBuildingBank, IconCirclePlus, IconCurrency, IconInvoice, IconLabel, IconLocation, IconNetwork, IconWallet } from '@tabler/icons-vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';

const visible = ref(false);

//country
const countries = ref([]);
const selectedCountry = ref();
const loadingCountries = ref(false);
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

//name input
const nameLabel = ref('receiver_name')

//type
const selectedType = ref('bank');
const types = ref([ //type options
    {
        name_label: 'receiver_name',
        value: 'bank',
    },
    {
        name_label: 'wallet_name',
        value: 'crypto'
    },
]);

//network
const selectedNetwork = ref();
const networks = ref([
    { name: 'TRC20'},
    { name: 'ERC20'},
    { name: 'BEP20'},
]);

watch(selectedType, (newType) => {
    const selected = types.value.find(type => type.value === newType);
    nameLabel.value = selected ? selected.name_label : '';
});

const openDialog = () => {
    visible.value = true;
    getCountries();
}

const closeDialog = () => {
    visible.value = false;
}

const form = useForm({
    name: '',
    type: '',
    bank_name: '',
    bank_branch: '',
    account_number: '',
    currency: '',
    crypto_tether: '',
    crypto_network: '',
});

const toast = useToast();

const submitForm = () => {
    form.type = selectedType.value;

    if(form.type === 'bank'){
        form.currency = selectedCountry.value;
        form.crypto_tether = '';
        form.crypto_network = '';
    } else {
        form.crypto_network = selectedNetwork.value;
        form.bank_name = '';
        form.bank_branch = '';
        form.currency = 'USD'
    }

    form.post(route('settings.addDepositProfile'), {
        onSuccess: () => {
            closeDialog();
            form.reset();

            toast.add({
                severity: 'success',
                summary: trans('public.success'),
                detail: trans('public.toast_add_deposit_profile_success'),
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
    <Button
        class="w-full md:w-auto"
        @click="openDialog"
    >
        <IconCirclePlus size="20" stroke-width="1.5" />
        <span class="pl-2">{{ $t('public.add_deposit_profile') }}</span>
    </Button>

    <Dialog v-model:visible="visible" modal :header="$t('public.add_deposit_profile')" class="dialog-xs md:dialog-md">
        <form @submit.prevent="submitForm" class="flex flex-col gap-6 items-center self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.type')" for="type"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconWallet :size="20" stroke-width="1.5"/>
                            </template>
                            <Select
                                inputId="type"
                                v-model="selectedType"
                                :options="types"
                                optionValue="value"
                                :placeholder="$t('public.select_type')" 
                                class="pl-7 block w-full" 
                                :invalid="!!form.errors.type" 
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center">
                                        <div>{{ $t(`public.${slotProps.value}`) }}</div>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex items-center gap-1 max-w-[220px] truncate">
                                        <span>{{ $t(`public.${slotProps.option.value}`) }}</span>
                                    </div>
                                </template>
                            </Select>
                        </InputIconWrapper>
                        <InputError :message="form.errors.type"/>
                    </div>
        
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t(`public.${nameLabel}`)"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconLabel :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText 
                                id="name"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.name"
                                :placeholder="$t(`public.${nameLabel}`)"
                                :invalid="!!form.errors.name"
                                autofocus
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.name"/>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t(`public.${selectedType}_details`) }}</span>

                <!-- bank -->
                <div v-if="selectedType === 'bank'" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.bank_name')" for="bank_name"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconBuildingBank :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText 
                                id="bank_name"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.bank_name"
                                :placeholder="$t('public.bank_name')"
                                :invalid="!!form.errors.bank_name"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.bank_name"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.bank_branch')" for="bank_branch"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconLocation :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText 
                                id="bank_branch"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.bank_branch"
                                :placeholder="$t('public.bank_branch')"
                                :invalid="!!form.errors.bank_branch"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.bank_branch"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.account_number')" for="account_number"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconInvoice :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText 
                                id="account_number"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.account_number"
                                :placeholder="$t('public.account_number')"
                                :invalid="!!form.errors.account_number"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.account_number"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.currency')" for="currency"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconCurrency :size="20" stroke-width="1.5"/>
                            </template>
                            <Select 
                                v-model="selectedCountry" 
                                :options="countries"
                                :loading="loadingCountries"
                                optionLabel="name"
                                :placeholder="$t('public.select_currency')"
                                class="pl-7 block w-full"
                                :invalid="!!form.errors.currency"
                                :filter-fields="['name', 'iso2', 'currency']"
                                filter
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center">
                                        <div>{{ slotProps.value.currency }}</div>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
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
                                        <div class="max-w-[200px] truncate">
                                            {{ slotProps.option.currency }} - <span class="text-surface-500">{{ slotProps.option.currency_symbol }}</span>
                                        </div>
                                    </div>
                                </template>
                            </Select>
                        </InputIconWrapper>
                        <InputError :message="form.errors.currency"/>
                    </div>
                </div>

                <!-- crypto -->
                <div v-if="selectedType === 'crypto'" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.crypto_tether')" for="crypto_tether"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconBrandTether :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText 
                                id="crypto_tether"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.crypto_tether"
                                :placeholder="$t('public.crypto_tether_placeholder')"
                                :invalid="!!form.errors.crypto_tether"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.crypto_tether"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.crypto_network')" for="crypto_network"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconNetwork :size="20" stroke-width="1.5"/>
                            </template>
                            <Select 
                                v-model="selectedNetwork" 
                                :options="networks"
                                optionLabel="name"
                                optionValue="name"
                                :placeholder="$t('public.select_crypto_network')"
                                class="pl-7 block w-full"
                                :invalid="!!form.errors.crypto_network"
                            >
                             
                            </Select>
                        </InputIconWrapper>
                        <InputError :message="form.errors.crypto_network"/>
                    </div>

                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel :value="$t('public.wallet_address')" for="wallet_address"/>
                        <InputIconWrapper>
                            <template #icon>
                                <IconInvoice :size="20" stroke-width="1.5"/>
                            </template>
                            <InputText 
                                id="wallet_address"
                                type="text"
                                class="pl-10 block w-full"
                                v-model="form.account_number"
                                :placeholder="$t('public.wallet_address')"
                                :invalid="!!form.errors.account_number"
                            />
                        </InputIconWrapper>
                        <InputError :message="form.errors.account_number"/>
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