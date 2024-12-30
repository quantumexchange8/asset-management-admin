<script setup>
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import RadioButton from 'primevue/radiobutton';
import Textarea from 'primevue/textarea';
import { IconCreditCard } from '@tabler/icons-vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    user: {
        type: Object
    },
    wallet: {
        type: Object,
    }
});
console.log(props.wallet);
const visible = ref(false);
const amount = ref(0);

const form = useForm({
    wallet_id: null,
    user_role: props.user.role,
    action: 'deposit',
    fund_type: 'demo_fund',
    amount: null,
    remarks: null,
});

const toast = useToast();

const submitForm = () => {
    if(props.wallet){
        form.wallet_id = props.wallet.id;
    }

    if(amount.value > 0){
        form.amount = amount.value;
    }

    form.post(route('member.detail.walletAdjustment'),{
        onSuccess:() => {
            visible.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Wallet adjust successfully!',
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
    <Toast />
    <Button @click="visible = true" type="button">
        <IconCreditCard size="20" stroke-width="1.5" />
    </Button>

    <Dialog v-model:visible="visible" modal header="Wallet Adjustment" :style="{ width: '25rem' }">
        <form @submit.prevent="submitForm">
            <div class="flex flex-col gap-5">
                <div
                    class="flex flex-col justify-center items-center px-8 py-4 gap-2 self-stretch bg-surface-100 dark:bg-surface-800">
                    <div class="text-surface-100 text-center text-xs font-medium">
                        {{ wallet.type.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()) }}
                    </div>
                    <div class="text-xl text-primary-500 font-semibold">
                        <span>{{ wallet.currency_symbol }}{{ wallet.balance }}</span>
                    </div>
                </div>

                <!-- action -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" value="Action" />
                    <div class="flex items-center gap-5">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton v-model="form.action" inputId="deposit" name="deposit" value="deposit"
                                    class="w-4 h-4" />
                            </div>
                            <label for="deposit">Deposit</label>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton v-model="form.action" inputId="withdrawal" name="withdrawal" value="withdrawal"
                                    class="w-4 h-4" />
                            </div>
                            <label for="withdrawal">Withdrawal</label>
                        </div>
                    </div>
                </div>

                <!-- fund type -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" value="Type" />
                    <div class="flex items-center gap-5">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton
                                    v-model="form.fund_type"
                                    inputId="demo_fund"
                                    value="demo_fund"
                                    class="w-4 h-4"
                                />
                            </div>
                            <label for="demo_fund">Demo Fund</label>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton
                                    v-model="form.fund_type"
                                    inputId="real_fund"
                                    value="real_fund"
                                    class="w-4 h-4"
                                    :disabled="['demo_fund', 'special_fund', 'special_demo_fund'].includes(user.role)"
                                />
                            </div>
                            <label for="real_fund">Real Fund</label>
                        </div>
                    </div>
                </div>

                <!-- amount -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="amount"
                        value="amount"
                        :invalid="form.errors.amount"
                    />
                    <InputNumber
                        v-model="amount"
                        inputId="amount"
                        :prefix="wallet.currency_symbol !== 'point' ? wallet.currency_symbol : ''"
                        :suffix="wallet.currency_symbol === 'point' ? wallet.currency_symbol : ''"
                        class="w-full"
                        inputClass="py-3 px-4"
                        :min="0"
                        :step="100"
                        showButtons
                        :minFractionDigits="2"
                        fluid
                        autofocus
                        :invalid="form.errors.amount"
                    />
                    <InputError :message="form.errors.amount" />
                </div>

                <!-- remarks -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="remarks"
                        value="Remarks"
                        :invalid="form.errors.remarks"
                    />
                    <Textarea
                        id="remarks"
                        type="text"
                        class="flex flex-1 self-stretch"
                        v-model="form.remarks"
                        placeholder="Enter Remarks"
                        :invalid="form.errors.remarks"
                        rows="5"
                        cols="30"
                    />
                    <InputError :message="form.errors.remarks" />
                </div>
            </div>

            <div class="flex justify-end items-center pt-10 md:pt-7 gap-3 md:gap-4 self-stretch">
                <Button type="button" label="Cancel" severity="secondary" @click="visible = false" class="flex flex-1 md:flex-none md:w-[120px]"></Button>
                <Button type="submit" label="Save" :disabled="form.processing" class="flex flex-1 md:flex-none md:w-[120px]"></Button>
            </div>
        </form>
    </Dialog>
</template>