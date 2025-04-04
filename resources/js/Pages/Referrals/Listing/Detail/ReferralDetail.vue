<script setup>
import ReferralInfo from './ReferralInfo.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { ref } from 'vue';
import { IconFileSearch } from '@tabler/icons-vue';
import ReferralInfoTable from './ReferralInfoTable.vue';

const props = defineProps({
    referral: Object,
})

const visible = ref(false);
</script>

<template>
    <Button
        type="button"
        severity="secondary"
        size="small"
        rounded
        outlined
        class="!p-2"
        @click="visible = true"
    >
        <IconFileSearch size="16" stroke-width="1.5" />
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
<!-- 
            <ReferralInfoTable 
                :referral="referral"
            /> -->
        </div>

    </Dialog>
</template>