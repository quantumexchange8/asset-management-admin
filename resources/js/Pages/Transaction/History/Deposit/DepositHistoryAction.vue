<script setup>
import { h, ref } from "vue";
import Button from "@/Components/Button.vue";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import {
    IconDotsVertical,
    IconFileDescription
} from "@tabler/icons-vue";
import DepositHistoryDetail from "./Detail/DepositHistoryDetail.vue";

const props = defineProps({
    depositHistory: Object,
});

const menu = ref();
const visible = ref(false);

const dialogType = ref();

const items = ref([
    {
        label: 'details',
        icon: h(IconFileDescription),
        command: () => {
            visible.value = true;
            dialogType.value = 'details'
        },
    },
]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <Button
        variant="gray-text"
        size="sm"
        type="button"
        iconOnly
        pill
        @click="toggle"
        aria-haspopup="true"
        aria-controls="overlay_tmenu"
    >
        <IconDotsVertical size="16" stroke-width="1.5" color="#667085" />
    </Button>

    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props, hasSubmenu }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
            >
                <component :is="item.icon" size="20" stroke-width="1.5" />
                <span class="font-medium">{{ $t(`public.${item.label}`) }}</span>
            </div>
        </template>
    </TieredMenu>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${dialogType}`)"
        class="dialog-xs md:dialog-md"
    >
        <template v-if="dialogType === 'details'">
            <DepositHistoryDetail
                :depositHistory="props.depositHistory"
            />
        </template>
    </Dialog>
</template>