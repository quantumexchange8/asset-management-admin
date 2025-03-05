<script setup>
import { h, ref } from "vue";
import Button from "primevue/button";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import {useConfirm} from "primevue/useconfirm";
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
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    member: Object,
});

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        delete_item: {
            group: 'headless-error',
            header: trans('public.delete_member'),
            text: trans('public.delete_member_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('member.deleteMember', props.member.id), {
                    method: 'put',
                    data: {
                        id: props.member.id,
                    },
                    onSuccess: () => {
                        toast.add({
                            severity: 'success',
                            summary: trans('public.success'),
                            detail: trans('public.toast_delete_member'),
                            life: 4000,
                        });
                    },
                    onError: (errors) => {
                        console.error(errors);
                    }
                })

                    .value = !checked.value;
            }
        },
    };

    const { group, header, text, dynamicText, suffix, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
};

const handleStatus = () => {
    if (props.member.deleted_at === null) {
        requireConfirmation('delete_item')
    }
}

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
        command: () => {
            handleStatus();
        }
    },

]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <Button
        severity="secondary"
        size="small"
        type="button"
        rounded
        text
        class="!p-2"
        @click="toggle"
        aria-haspopup="true"
        aria-controls="overlay_tmenu"
    >
        <IconDotsVertical size="16" stroke-width="1.5" />
    </Button>

    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props, hasSubmenu }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
            >
                <div
                    :class="{
                        'text-surface-400 dark:text-surface-500': item.label !== 'delete',
                        'text-red-500': item.label === 'delete',
                    }"
                >
                    <component
                        :is="item.icon"
                        size="20"
                        stroke-width="1.5"
                    />
                </div>

                <span
                    class="font-medium"
                    :class="{'text-red-500': item.label === 'delete'}"
                >{{ $t(`public.${item.label}`) }}</span>
                <span v-if="hasSubmenu" class="ml-auto">
                    <IconChevronRight size="20" stroke-width="1.5" />
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
            />
        </template>

        <template v-if="dialogType === 'change_upline'">
            <ChangeUpline
                :member="member"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>

</template>



