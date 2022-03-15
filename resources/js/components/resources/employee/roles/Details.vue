<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Права входящие в группу')"
            name="permissions">
            <section class="flex-content">
                <details-permissions :role="role" />
            </section>
        </el-tab-pane>
        <el-tab-pane
            v-if="$can(['employees.access', 'employees.access-clinic'])"
            :lazy="true"
            :label="__('Сотрудники входящие в группу')"
            name="users">
            <section class="flex-content">
                <details-employees :role="role" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import DetailsPermissions from './DetailsPermissions.vue';
import DetailsEmployees from './DetailsEmployees.vue';

export default {
    components: {
        DetailsPermissions,
        DetailsEmployees,
    },
    props: {
        role: Object,
    },
    data() {
        return {
            activeTab: 'permissions',
        };
    },
}
</script>