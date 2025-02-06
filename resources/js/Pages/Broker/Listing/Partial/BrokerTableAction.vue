<script setup>
import { h, ref } from "vue";
import Button from "@/Components/Button.vue";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import {
    IconDotsVertical,
    IconId,
    IconChevronRight,
    IconEdit
} from "@tabler/icons-vue";
import EditBrokerInfo from "../Detail/EditBrokerInfo.vue";

const props = defineProps({
    broker: Object,
    locales: Array,
});

const menu = ref();
const visible = ref(false);

const dialogType = ref();

const items = ref([
    {
        label: 'Broker Detail',
        icon: h(IconId), 
        command: () => {
            window.location.href = `/broker/detail/${props.broker.id}`;
        },
    },
    {
        label: 'Edit',
        icon: h(IconEdit),
        command: () => {
            visible.value = true;
            dialogType.value = 'edit'
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
                <component :is="item.icon" size="20" stroke-width="1.25" :color="item.label === 'Delete' ? '#F04438' : '#667085'"  />
                <span class="font-medium" :class="{'text-error-500': item.label === 'Delete'}">{{ item.label }}</span>
                <span v-if="hasSubmenu" class="ml-auto">
                    <IconChevronRight size="20" stroke-width="1.25" />
                </span>
            </div>
        </template>
    </TieredMenu>

    <Dialog
        v-model:visible="visible"
        modal
         class="dialog-xs md:dialog-md"
        style="width: 90%; max-width: 45rem;"
    
    >
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    {{dialogType.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())}}
                </div>
            </div>
        </template>

        <template v-if="dialogType === 'edit'">
            <EditBrokerInfo
                :broker="props.broker"
                :locales="props.locales"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>
</template>