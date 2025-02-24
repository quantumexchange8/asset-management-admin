<script setup>
import { h, ref } from "vue";
import Button from "@/Components/Button.vue";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import {
    IconDotsVertical,
    IconId,
    IconTrash,
    IconChevronRight,
} from "@tabler/icons-vue";
import EditDepositProfile from "./Detail/EditDepositProfile.vue";

const props = defineProps({
    depositProfile: Object,
})

const menu = ref();
const visible = ref(false);

// type of dialog spawn (rank or role)
const dialogType = ref();

const items = ref([
    {
        label: 'edit',
        icon: h(IconId),
        command: () => {
            visible.value = true;
            dialogType.value = 'edit';
        },
    },
    {
        separator: true
    },
    {
        label: 'delete',
        icon: h(IconTrash),
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
        <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
    </Button>

    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props, hasSubmenu }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
            >
                <component :is="item.icon" size="20" stroke-width="1.25" :color="item.label === 'delete' ? '#F04438' : '#667085'"  />
                <span class="font-medium" :class="{'text-error-500': item.label === 'delete'}">{{ $t(`public.${item.label}`) }}</span>
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
    >
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    {{$t(`public.${dialogType}`)}}
                </div>
            </div>
        </template>

        <template v-if="dialogType === 'edit'">
            <EditDepositProfile
                :depositProfile="depositProfile"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>
</template>