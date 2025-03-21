<script setup>
import { defineProps, defineEmits, computed } from "vue";
import Button from "primevue/button";
import { IconPlus, IconMinus } from "@tabler/icons-vue";
import ReferralDetail from "../Detail/ReferralDetail.vue";
import Tag from "primevue/tag";
import { generalFormat } from '@/Composables/format';
import { useLangObserver } from "@/Composables/localeObserver";

const props = defineProps({
    downlines: Array,
    expandedUsers: Object, // Reactive expansion state from parent
    level: Number,
});

const { formatAmount } = generalFormat();
const { locale } = useLangObserver();

const emit = defineEmits(["update:expandedUsers"]);

// Function to toggle expansion for downlines
const toggleExpand = (id) => {
    if (!id) return;
    
    const updatedExpandedUsers = { ...props.expandedUsers, [id]: !props.expandedUsers[id] };
    emit("update:expandedUsers", updatedExpandedUsers);
};

const getUniqueColor = (level) => {
    const goldenRatio = 0.6180339887; // Golden ratio for better distribution
    const hue = (level * goldenRatio * 360) % 360; // Spread hue values evenly
    return `hsl(${hue}, 70%, 50%)`; // Adjust saturation & lightness
};

const levelColor = computed(() => getUniqueColor(props.level));
</script>
<template>
    <div v-for="downline in downlines" :key="downline?.id">
        <div 
            v-if="downline?.id" 
            :class="[
               'p-4 rounded-lg bg-surface-200 dark:bg-surface-700 flex justify-between items-center overflow-x-auto mt-4',
               locale === 'en' ? 'max-w-[1200px] min-w-[1200px]' : 'max-w-[950px] min-w-[950px]',
            ]"
        >
            <div class="flex items-center gap-6 w-full relative">
                <!-- Left: Expand Button -->
                <Button
                    v-if="downline?.children?.length"
                    @click="toggleExpand(downline?.id)"
                    class="w-10 h-10"
                    rounded
                >
                    <IconPlus v-if="!expandedUsers?.[downline?.id]" class="w-5 h-5" />
                    <IconMinus v-else class="w-5 h-5" />
                </Button>

                <!-- Level Indicator -->
                <div
                    :style="{ backgroundColor: levelColor }"
                    class="w-8 h-8 flex items-center justify-center text-white font-semibold rounded-full text-sm"
                >
                    {{ level }}
                </div>

                <!-- Grid for Downline Info & Other Details -->
                <div :class="[
                    'gap-x-6 gap-y-2 text-sm pr-7',
                    locale === 'en' ? 'grid grid-cols-[1.5fr_1fr_2fr_2fr_1fr_1fr]' : 'grid grid-cols-[3fr_1.5fr_2.5fr_2.5fr_1fr_1fr]',
            
                ]">
                    <!-- Name & Email -->
                    <div class="flex flex-col items-start">
                        <span class="font-medium text-surface-950 dark:text-white truncate">{{ downline?.name }}</span>
                        <span class="text-surface-500 text-sm truncate">{{ downline?.email }}</span>
                    </div>

                    <!-- Rank -->
                    <div class="flex flex-col items-start">
                        <span class="font-semibold">{{ $t('public.rank') }}</span>
                        <Tag
                            severity="secondary"
                            :value="downline.rank.rank_name === 'member' ? $t(`public.${downline.rank.rank_name}`) : downline.rank.rank_name"
                        />
                    </div>

                    <!-- Personal Fund -->
                    <div class="flex flex-col items-start">
                        <span class="font-semibold">{{ $t('public.personal_capital_fund') }} ($)</span>
                        <span class="dark:text-surface-400">{{ formatAmount(downline.total_personal_fund) }}</span>
                    </div>

                    <!-- Team Fund -->
                    <div class="flex flex-col items-start">
                        <span class="font-semibold">{{ $t('public.team_capital_fund') }} ($)</span>
                        <span class="dark:text-surface-400">{{ formatAmount(downline.total_team_fund) }}</span>
                    </div>

                    <!-- Direct Downlines -->
                    <div class="flex flex-col items-start whitespace-nowrap">
                        <span class="font-semibold">{{ $t('public.direct_downlines') }}</span>
                        <span class="dark:text-surface-400">{{ downline.downlines_count }}</span>
                    </div>

                    <div class="flex flex-col items-start">
                        <ReferralDetail :referral="downline" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recursively show children when expanded -->
        <Transition
            enter-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="opacity-0 translate-y-2 scale-y-95"
            enter-to-class="opacity-100 translate-y-0 scale-y-100"
            leave-active-class="transition-all duration-200 ease-in-out"
            leave-from-class="opacity-100 translate-y-0 scale-y-100"
            leave-to-class="opacity-0 translate-y-2 scale-y-95"
        >
            <div v-if="expandedUsers?.[downline?.id]" class="mt-3 pl-8 border-l border-surface-300">
                <ReferralDownline
                    v-if="downline?.children?.length"
                    :downlines="downline.children"
                    :expandedUsers="expandedUsers"
                    @update:expandedUsers="emit('update:expandedUsers', $event)"
                    :level="level + 1"
                />
            </div>
        </Transition>
    </div>
</template>
