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
    IconSitemap
} from "@tabler/icons-vue";
import UpgradeRank from "./Partial/UpgradeRank.vue";
import ChangeUpline from "./Partial/ChangeUpline.vue";

const props = defineProps({
    member: Object,
})

const menu = ref();
const visible = ref(false);

// type of dialog spawn (rank or role)
const dialogType = ref();

const items = ref([
    {
        label: 'member_detail',
        icon: h(IconId),
        command: () => {
            window.location.href = `/member/detail/${props.member.id_number}`;
        },
    },
    {
        label: 'access_portal',
        icon: h(IconDeviceLaptop),
        command: () => {
            window.open(route('member.access_portal', props.member.id), '_blank');
        }
    },
    {
        label: 'change_upline',
        icon: h(IconSitemap),
        command: () => {
            visible.value = true;
            dialogType.value = 'change_upline'
        },
    },
    {
        label: 'upgrade',
        icon: h(IconUserUp),
        items: [
            {
                label: 'rank',
                icon: h(IconUserCog),
                command: () => {
                    visible.value = true;
                    dialogType.value = 'upgrade_rank';
                }
            },
        ]
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
        class="dialog-xs md:dialog-sm"
    >
        <template #header>
            <div class="flex items-center gap-4">
                <div class="text-xl font-bold">
                    {{$t(`public.${dialogType}`)}}
                </div>
            </div>
        </template>

        <template v-if="dialogType === 'upgrade_rank'">
            <UpgradeRank
                :member="member"
                @update:visible="visible = false"
                @rank-updated="(newRank) => $emit('rank-updated', member.id, newRank)" 
            />
        </template>

        <template v-if="dialogType === 'change_upline'">
            <ChangeUpline
                :member="member"
                @update:visible="visible = false"
                @upline-updated="(newUpline) => $emit('upline-updated', member.id, newUpline)" 
            />
        </template>
    </Dialog>

</template>



