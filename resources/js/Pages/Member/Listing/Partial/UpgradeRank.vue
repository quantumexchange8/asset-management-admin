<script setup>
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from 'primevue/select';
import InputError from '@/Components/InputError.vue';
import Button from 'primevue/button';
import { onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { IconUserUp } from '@tabler/icons-vue';

const props = defineProps({
    member: {
        type: Object,
    }
});

const ranks = ref([]);
const selectedRank = ref(props.member.rank);

const loadingRanks = ref(false);

//close dialog after save and refresh rank after save
const emit = defineEmits(['update:visible']);

const getRanks = async () => {
    loadingRanks.value = true;

    try{
        const response = await axios.get('/get_ranks');
        ranks.value = response.data.ranks;
    } catch (error){
        console.error('Error fetching selectedRank:', error);
    } finally {
        loadingRanks.value = false;
    }
}

onMounted(() => {
    getRanks();
});

const form = useForm({
    user_id: props.member.id,
    rank: null,
});

const toast = useToast();

const submitForm = () => {
    form.rank = selectedRank.value;
    form.put(route('member.upgradeRank'), {
        onSuccess:() => {
            emit('update:visible', false);
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Rank upgraded successfully!',
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="flex flex-col gap-5 items-center self-stretch">
            <div class="flex items-center gap-3 self-stretch">
                <div class="w-8 h-8 rounded-full overflow-hidden grow-0 shrink-0">
                    <DefaultProfilePhoto />
                </div>
                <div class="flex flex-col items-start">
                    <div class="text-sm font-medium">
                        {{ member.name }}
                    </div>
                    <div class="text-gray-500 text-xs">
                        @{{ member.username }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 self-stretch">
                <span class="font-semibold text-sm">Select New Rank</span>
                <div class="space-y-2">
                    <InputLabel
                        for="rank"
                        value="Rank"
                    />
                    <InputIconWrapper>
                        <template #icon>
                            <IconUserUp :size="20" stroke-width="1.5"/>
                        </template>

                        <Select
                            v-model="selectedRank"
                            :options="ranks"
                            :loading="loadingRanks"
                            optionLabel="name"
                            placeholder="Select Rank"
                            class="pl-7 block w-full"
                            :invalid="form.errors.rank"
                            filter
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.rank_name }}</div>
                                </div>
                                <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span> 
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1 max-w-[220px] truncate">
                                    <span>{{ slotProps.option.rank_name }}</span>
                                </div>
                            </template>
                        </Select>
                    </InputIconWrapper>
                    <InputError :message="form.errors.rank"/>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
            <Button type="button" label="Cancel" severity="secondary" @click="$emit('update:visible', false)"></Button>
            <Button type="submit" label="Save" :disabled="form.processing"></Button>
        </div>
    </form>
</template>