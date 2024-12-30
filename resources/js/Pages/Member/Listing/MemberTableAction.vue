<script setup>
import { h, ref } from "vue";
import Button from "@/Components/Button.vue";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import {
    IconDotsVertical,
    IconId,
    IconUserUp,
    IconTrash,
    IconDeviceLaptop,
    IconChevronRight,
    IconUserCog,
    IconUserDollar,
} from "@tabler/icons-vue";
import UpgradeRank from "./Partial/UpgradeRank.vue";

const props = defineProps({
    member: Object,
})

const menu = ref();
const visible = ref(false);

// type of dialog spawn (rank or role)
const dialogType = ref();

const items = ref([
    {
        label: 'Member Detail',
        icon: h(IconId), 
        command: () => {
            window.location.href = `/member/detail/${props.member.id_number}`;
        },
    },
    {
        label: 'Access Portal',
        icon: h(IconDeviceLaptop),
    },
    {
        label: 'Upgrade',
        icon: h(IconUserUp),
        items: [
            {
                label: 'Rank',
                icon: h(IconUserCog),
                command: () => {
                    visible.value = true;
                    dialogType.value = 'upgrade_rank';
                }
            },
            {
                label: 'Role',
                icon: h(IconUserDollar),
            }
        ]
    },
    {
        separator: true
    },
    {
        label: 'Delete',
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
        header="Upgrade Rank"
        :style="{ width: '20rem' }"
    >
        <template v-if="dialogType === 'upgrade_rank'">
            <UpgradeRank
                :member="member"
                @update:visible="visible = false"
            />

        </template>
    </Dialog>
        
</template>



