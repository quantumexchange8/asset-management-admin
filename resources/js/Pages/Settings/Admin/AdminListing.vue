<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AdminTable from "@/Pages/Settings/Admin/AdminTable.vue";
import AddAdmin from "@/Pages/Settings/Admin/AddAdmin.vue";

defineProps({
    permissions: Object,
    permissionsCount: Number
})
</script>

<template>
    <AuthenticatedLayout title="admin_listing">
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <AddAdmin
                    :permissions="permissions"
                />
            </div>

            <AdminTable
                :permissions="permissions"
                :permissionsCount="permissionsCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
