<script setup>
import {IconSearch, IconX, IconChevronUp, IconMinus} from "@tabler/icons-vue";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Card from "primevue/card";
import Skeleton from "primevue/skeleton";
import {ref, watch} from "vue";
import debounce from "lodash/debounce.js";
import {generalFormat} from "@/Composables/format.js";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import EmptyData from "@/Components/EmptyData.vue";

const emit = defineEmits();

const search = ref('');
const checked = ref(true);
const upline = ref(null);
const parents = ref([]);
const upline_id = ref();
const parent_id = ref();
const children = ref([]);
const loading = ref(false);
const success = ref(false);
const { formatAmount } = generalFormat();

const getNetwork = async (filterUplineId = upline_id.value, filterParentId = parent_id.value,  filterSearch = search.value) => {
    loading.value = true;
    try {
        let url = `/referral/getDownlineData?search=` + filterSearch;

        if (filterUplineId) {
            url += `&upline_id=${filterUplineId}`;
        }

        if (filterParentId) {
            url += `&parent_id=${filterParentId}`;
        }


        const response = await axios.get(url);

        success.value = response.data.success;
        upline.value = response.data.upline;
        parents.value = response.data.parents;
        children.value = response.data.children;

        // Check upline first
        if (!response.data.success) {
            console.log(response.data.message);
        }

    } catch (error) {
        console.error('Error get network:', error);
    } finally {
        loading.value = false;
    }
};

getNetwork();

watch(search,
    debounce((newSearchValue) => {
        getNetwork(upline_id.value, parent_id.value, newSearchValue)
    }, 300)
);

 // Store selected children
 const showChildren = (parentId) => {
    parent_id.value = parentId;
    search.value = '';

    getNetwork(null, parent_id.value);
}

const selectDownline = (uplineId, downlineId) => {
    upline_id.value = uplineId;
    parent_id.value = downlineId;
    search.value = '';

    getNetwork(upline_id.value, parent_id.value);
}

const collapseAll = () => {
    upline_id.value = null;
    parent_id.value = null;
    search.value = '';
    
    getNetwork(); // Load only the default Parents section
};

const backToUpline = (parentLevel) => {
    if (parents.value.length === 0) return; // Ensure parents exist

    if (parentLevel === 1) {
        upline_id.value = null;
        parent_id.value = null;
        search.value = '';
    } else {
        parent_id.value = parents.value[0].upline_id ?? null;
        upline_id.value = parents.value[0].upper_upline_id ?? null;
        search.value = '';
    }

    getNetwork(upline_id.value, parent_id.value);
};


const clearSearch = () => {
    search.value = '';
}
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-5 items-center self-stretch">
                <div class="flex flex-col md:flex-row gap-3 items-center self-stretch">
                    <div class="relative w-full md:w-60">
                        <InputIconWrapper>
                            <template #icon>
                                <IconSearch :size="20" stroke-width="1.5"/>
                            </template>

                            <InputText
                                id="name"
                                type="text"
                                class="pl-10 block w-full md:w-60"
                                v-model="search"
                                :placeholder="$t('public.search_keyword')"
                            />
                        </InputIconWrapper>
                        <div
                            v-if="search"
                            class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                            @click="clearSearch"
                        >
                            <IconX size="16" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 w-full gap-3">
                        <div class="w-full flex justify-end">
                            <Button
                                size="small"
                                @click="collapseAll"
                                class="w-full md:w-auto flex gap-1"
                            >
                                <IconMinus size="20" stroke-width="1.25" />
                                {{ $t('public.collapse_all') }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div
                    v-if="loading"
                    class="flex flex-col items-center gap-6 w-full"
                >
                    <!-- Upline Section -->
                    <div class="flex flex-col items-center gap-6 w-full">
                        <div class="flex items-center self-stretch gap-3">
                            <span class="text-sm font-medium text-surface-400 dark:text-surface-500 uppercase">{{ $t('public.level' ) }} {{ 0 }}</span>
                            <div class="h-[1px] flex-1 bg-surface-200 dark:bg-surface-700" />
                        </div>

                        <div class="flex justify-center flex-wrap w-full">
                            <div
                                class="rounded flex flex-col items-center w-full md:max-w-[240px] shadow-card border-l-4 border border-surface-200 dark:border-surface-700 select-none cursor-pointer md:basis-1/2 bg-white dark:bg-surface-800"
                            >
                                <div class="pt-3 pb-2 px-3 rounded-t flex flex-col w-full self-stretch">
                                    <Skeleton width="5rem" class="my-0.5" height="1rem"></Skeleton>
                                    <Skeleton width="7rem" class="my-0.5" height="1rem"></Skeleton>
                                    <Skeleton width="9rem" class="my-0.5" height="1rem"></Skeleton>
                                </div>
                                <div class="pb-2 px-3 rounded-b flex items-center gap-3 w-full self-stretch">
                                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                                        <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                                    </div>
                                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                                        <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Section -->
                    <div class="flex flex-col items-center gap-6 w-full">
                        <div class="flex items-center self-stretch gap-3">
                            <span class="text-sm font-medium text-surface-400 dark:text-surface-500 uppercase">{{ $t('public.level' ) }} {{ 1 }}</span>
                            <div class="h-[1px] flex-1 bg-surface-200 dark:bg-surface-700" />
                        </div>

                        <!-- loading state -->
                        <div class="flex justify-center flex-wrap w-full">
                            <div
                                class="rounded flex flex-col items-center w-full md:max-w-[240px] shadow-card border-l-4 border border-surface-200 dark:border-surface-700 select-none cursor-pointer md:basis-1/2 bg-white dark:bg-surface-800"
                            >
                                <div class="pt-3 pb-2 px-3 rounded-t flex flex-col w-full self-stretch">
                                    <Skeleton width="5rem" class="my-0.5" height="1rem"></Skeleton>
                                    <Skeleton width="7rem" class="my-0.5" height="1rem"></Skeleton>
                                    <Skeleton width="9rem" class="my-0.5" height="1rem"></Skeleton>
                                </div>
                                <div class="pb-2 px-3 rounded-b flex items-center gap-3 w-full self-stretch">
                                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                                        <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                                    </div>
                                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                                        <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Children Section -->
                    <div class="flex flex-col items-center gap-6 w-full">
                        <div class="flex items-center self-stretch gap-3">
                            <span class="text-sm font-medium text-surface-400 dark:text-surface-500 uppercase">{{ $t('public.level' ) }} {{ 2 }}</span>
                            <div class="h-[1px] flex-1 bg-surface-200 dark:bg-surface-700" />
                        </div>

                        <!-- loading state -->
                        <div class="flex justify-center flex-wrap w-full">
                            <div
                                class="rounded flex flex-col items-center w-full md:max-w-[240px] shadow-card border-l-4 border border-surface-200 dark:border-surface-700 select-none cursor-pointer md:basis-1/2 bg-white dark:bg-surface-800"
                            >
                                <div class="pt-3 pb-2 px-3 rounded-t flex flex-col w-full self-stretch">
                                    <Skeleton width="5rem" class="my-0.5" height="1rem"></Skeleton>
                                    <Skeleton width="7rem" class="my-0.5" height="1rem"></Skeleton>
                                    <Skeleton width="9rem" class="my-0.5" height="1rem"></Skeleton>
                                </div>
                                <div class="pb-2 px-3 rounded-b flex items-center gap-3 w-full self-stretch">
                                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                                        <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                                    </div>
                                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                                        <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="w-full"
                >
                    <div
                        v-if="success"
                        class="flex flex-col items-center gap-6 w-full"
                    >
                        <!-- Upline Section -->
                        <div v-if="checked && upline" class="flex flex-col items-center gap-6 w-full">
                            <div class="flex items-center self-stretch gap-3">
                                <span class="text-sm font-medium text-surface-400 dark:text-surface-500 uppercase">{{ $t('public.level' ) }} {{ upline.level ?? 0 }}</span>
                                <div class="h-[1px] flex-1 bg-surface-200 dark:bg-surface-700" />
                            </div>
                            <div class="flex justify-center flex-wrap w-full relative">
                                <div
                                    class="rounded flex flex-col items-center md:max-w-[215px] shadow-card border-l-4 select-none cursor-pointer md:basis-1/2 bg-white dark:bg-surface-800 w-full border-blue-500 border-t border-t-surface-200 dark:border-t-surface-700 border-b border-b-surface-200 dark:border-b-surface-700 border-r border-r-surface-200 dark:border-r-surface-700 hover:border-t hover:border-t-blue-500 hover:border-b hover:border-b-blue-500 hover:border-r hover:border-r-blue-500 transition-colors duration-200"
                                >
                                    <div class="pt-3 pb-2 px-3 rounded-t flex flex-col items-center w-full self-stretch">
                                        <div class="w-full text-sm font-semibold text-surface-950 dark:text-white truncate">
                                            {{ upline.username }}
                                        </div>
                                        <div class="w-full text-sm text-surface-400 truncate">
                                            {{ $t('public.fund') }}: <span class="font-semibold text-primary">{{ formatAmount(upline.capital_fund_sum) }}</span>
                                        </div>
                                        <div class="w-full text-sm text-surface-400 truncate">
                                            {{ $t('public.team_capital') }}: <span class="font-semibold text-primary">{{ formatAmount(upline.total_downline_capital_fund) }}</span>
                                        </div>
                                    </div>
                                    <div class="pb-2 px-3 rounded-b grid grid-cols-2 gap-3 w-full self-stretch text-sm">
                                        <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                            <span class="font-medium">{{ formatAmount(upline.total_directs, 0, '') }}</span>
                                            <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                                        </div>
                                        <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                            <span class="font-medium">{{ formatAmount(upline.total_downlines, 0, '') }}</span>
                                            <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parents Section -->
                        <div v-if="parents.length > 0" class="flex flex-col items-center gap-6 w-full">
                            <div class="flex items-center self-stretch gap-3">
                                <span class="text-sm font-medium text-surface-400 dark:text-surface-500 uppercase">{{ $t('public.level' ) }} {{ parents[0].level ?? 0 }}</span>
                                <div class="h-[1px] flex-1 bg-surface-200 dark:bg-surface-700" />
                            </div>
                            <div v-if="children.length > 0 && upline_id == null && !loading" class="absolute top-[250px]">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    rounded
                                    class="!px-2 z-10"
                                    @click="backToUpline(parents.level)"
                                >
                                    <IconChevronUp size="16" stroke-width="1.5"/>
                                </Button>
                            </div>
                            <div v-if="upline_id && !loading" class="absolute top-[460px]">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    rounded
                                    class="!px-2 z-10"
                                    @click="backToUpline(parents.level)"
                                >
                                    <IconChevronUp size="16" stroke-width="1.5"/>
                                </Button>
                            </div>
                            <div class="flex justify-center flex-wrap w-full relative gap-5">
                                <!-- Loop through each parent -->
                                <div
                                    v-for="parent in parents"
                                    :key="parent.id"
                                    @click="!upline_id && showChildren(parent.id, parent.children)"
                                    :class="[
                                        'rounded flex flex-col items-center md:max-w-[215px] shadow-card border-l-4 select-none w-full md:basis-1/3 xl:basis-1/4 bg-white dark:bg-surface-800 border-primary border-t border-t-surface-200 dark:border-t-surface-700 border-b border-b-surface-200 dark:border-b-surface-700 border-r border-r-surface-200 dark:border-r-surface-700 transition-all duration-200',
                                        { 
                                            'cursor-pointer hover:border-t hover:border-primary dark:hover:border-primary': !upline_id,
                                            'cursor-default': upline_id
                                        }
                                    ]"
                                >
                                    <div class="pt-3 pb-2 px-3 rounded-t flex flex-col items-center w-full self-stretch">
                                        <div class="w-full text-sm font-semibold text-surface-950 dark:text-white truncate">
                                            {{ parent.username }}
                                        </div>
                                        <div class="w-full text-sm text-surface-400 truncate">
                                            {{ $t('public.fund') }}:
                                            <span class="font-semibold text-primary">{{ formatAmount(parent.capital_fund_sum) }}</span>
                                        </div>
                                        <div class="w-full text-sm text-surface-400 truncate">
                                            {{ $t('public.team_capital') }}:
                                            <span class="font-semibold text-primary">{{ formatAmount(parent.total_downline_capital_fund) }}</span>
                                        </div>
                                    </div>

                                    <div class="pb-2 px-3 rounded-b grid grid-cols-2 gap-3 w-full self-stretch text-sm">
                                        <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                            <span class="font-medium">{{ formatAmount(parent.total_directs, 0, '') }}</span>
                                            <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                                        </div>
                                        <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                            <span class="font-medium">{{ formatAmount(parent.total_downlines, 0, '') }}</span>
                                            <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Children Section -->
                        <div v-if="children.length > 0" class="flex flex-col items-center gap-6 w-full">
                            <div class="flex items-center self-stretch gap-3">
                                <span class="text-sm font-medium text-surface-400 dark:text-surface-500 uppercase">{{ $t('public.level' ) }} {{ children[0].level ?? 0 }}</span>
                                <div class="h-[1px] flex-1 bg-surface-200 dark:bg-surface-700" />
                            </div>
                            <div class="grid grid-cols-2 md:flex gap-3 md:gap-5 justify-center flex-wrap w-full">
                                <div
                                    v-for="downline in children"
                                    :key="downline.id"
                                    @click="selectDownline(downline.upline_id, downline.id)"
                                    class="rounded flex flex-col items-center  md:max-w-[215px] shadow-card border-l-4 select-none cursor-pointer md:basis-1/3 xl:basis-1/4 bg-white dark:bg-surface-800 w-full border-primary border-t border-t-surface-200 dark:border-t-surface-700 border-b border-b-surface-200 dark:border-b-surface-700 border-r border-r-surface-200 dark:border-r-surface-700 hover:border-t hover:border-primary dark:hover:border-primary transition-all duration-200"
                                 
                                >
                                    <div class="pt-3 pb-2 px-3 rounded-t flex flex-col items-center w-full self-stretch">
                                        <div class="w-full text-sm font-semibold text-surface-950 dark:text-white truncate">
                                            {{ downline.username }}
                                        </div>
                                        <div class="flex flex-col md:flex-row w-full text-sm text-surface-400 truncate">
                                            {{ $t('public.fund') }}: <span class="font-semibold text-primary">{{ formatAmount(downline.capital_fund_sum) }}</span>
                                        </div>
                                        <div class="flex flex-col md:flex-row w-full text-sm text-surface-400 truncate">
                                            {{ $t('public.team_capital') }}: <span class="font-semibold text-primary">{{ formatAmount(downline.total_downline_capital_fund) }}</span>
                                        </div>
                                    </div>
                                    <div class="pb-2 px-3 rounded-b grid grid-cols-1 md:grid-cols-2 gap-3 w-full self-stretch text-sm">
                                        <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                            <span class="font-medium">{{ formatAmount(downline.total_directs, 0, '') }}</span>
                                            <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                                        </div>
                                        <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                                            <span class="font-medium">{{ formatAmount(downline.total_downlines, 0, '') }}</span>
                                            <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex justify-center items-center">
                        <EmptyData
                            :title="$t('public.no_user_found')"
                            :message="$t('public.no_user_found_caption')"
                        />
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
