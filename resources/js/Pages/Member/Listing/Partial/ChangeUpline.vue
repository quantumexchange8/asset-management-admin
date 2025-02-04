<script setup>
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from 'primevue/select';
import InputError from '@/Components/InputError.vue';
import Button from 'primevue/button';
import { computed, onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputIconWrapper from '@/Components/InputIconWrapper.vue';
import { IconUserCode } from '@tabler/icons-vue';

const props = defineProps({
    member: {
        type: Object,
    }
});

const emit = defineEmits(['update:visible']);

const users = ref([]);
const selectedUpline = ref();
const loadingUsers = ref(false);

const getUsers = async () => {
    loadingUsers.value = true;
    try {
        const response = await axios.get('/get_users');
        users.value = response.data.users;
    } catch (error) {
        console.error('Error fetching selectedUpline:', error);
    } finally {
        loadingUsers.value = false;
    }
}

onMounted(() => {
    getUsers();
});

// Function to get all downlines of a given user
const getDownlines = (userId) => {
    let downlines = [];
    
    const findDownlines = (currentUserId) => {
        users.value.forEach(user => {
            if (user.upline_id === currentUserId) {
                downlines.push(user.id);
                findDownlines(user.id); // Recursively find downlines
            }
        });
    }

    findDownlines(userId); //check each user in the users.value array to see if they have the current user as their upline
    return downlines;
};

const filteredUsers = computed(() => {
    const downlines = getDownlines(props.member.id);

    const validUsers = users.value.filter(user => {
        return user.id !== props.member.id && !downlines.includes(user.id);
    });

    // Create a deep copy of validUsers to avoid Vue proxying issues
    const plainValidUsers = JSON.parse(JSON.stringify(validUsers));

    return plainValidUsers;
});

watch(filteredUsers, () => {
    selectedUpline.value = users.value.find(user => user.id === props.member.upline_id);
});

const form = useForm({
    user_id: props.member.id,
    upline: null,
});

const toast = useToast();

const submitForm = () => {
    form.upline = selectedUpline.value;
    form.put(route('member.changeUpline'), {
        onSuccess:() => {
            emit('update:visible', false);
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Upline changed successfully!',
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
                <span class="font-semibold text-sm">Select New Upline</span>
                <div class="space-y-2">
                    <InputLabel
                        for="upline"
                        value="Upline"
                    />
                    <InputIconWrapper>
                        <template #icon>
                            <IconUserCode :size="20" stroke-width="1.5"/>
                        </template>

                        <Select
                            v-model="selectedUpline"
                            :options="filteredUsers"
                            :loading="loadingUsers"
                            optionLabel="name"
                            placeholder="Select Upline"
                            class="pl-7 block w-full"
                            :invalid="form.errors.upline"
                            filter
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.name }}</div>
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span> 
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1 max-w-[220px] truncate">
                                    <span>{{ slotProps.option.name }}</span>
                                    <span class="text-xs text-gray-500">@{{ slotProps.option.username }}</span>
                                </div>
                            </template>
                        </Select>
                    </InputIconWrapper>
                    <InputError :message="form.errors.upline" />    
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
            <Button type="button" label="Cancel" severity="secondary" @click="$emit('update:visible', false)"></Button>
            <Button type="submit" label="Save" :disabled="form.processing"></Button>
        </div>
    </form>
</template>