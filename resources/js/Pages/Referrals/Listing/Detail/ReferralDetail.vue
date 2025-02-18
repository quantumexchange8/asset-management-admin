<script setup>
import ReferralInfo from './ReferralInfo.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { ref } from 'vue';
import { IconFileDescription } from '@tabler/icons-vue';
import ReferralInfoTable from './ReferralInfoTable.vue';

const props = defineProps({
    referral: Object,
})

const visible = ref(false);
</script>

<template>
    <Button
        class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
        @click="visible = true"
    >
        <IconFileDescription :size="20" stroke-width="1.5" :style="{ stroke: '#cca05a' }" />
    </Button>

    <Dialog v-model:visible="visible" modal class="dialog-xs md:dialog-lg">
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    Referrer Detail
                </div>
            </div>
        </template>

        <div class="flex flex-col items-center w-full gap-5 self-stretch">
            <ReferralInfo 
                :referral="referral"
            />

            <ReferralInfoTable 
                :referral="referral"
            />
        </div>

    </Dialog>
</template>