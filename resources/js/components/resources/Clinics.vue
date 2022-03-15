<template>
    <page
        :title="__('Клиники')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                v-if="$canAccess('clinics')"
                :label="__('Справочник клиник')"
                name="clinics" >
                <clinics />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                v-if="$canAccess('clinic-groups')"
                :label="__('Группы клиник')"
                name="clinic-groups" >
                <clinic-groups />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                v-if="$canAccess('money-recievers')"
                :label="__('Получатели денег')"
                name="money-recievers" >
                <money-recievers />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                v-if="$canAccess('departments')"
                :label="__('Отделения')"
                name="departments" >
                <section class="p-0 shrinkable">
                    <departments />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import Clinics from './clinic/Clinics.vue';
import ClinicGroups from './clinic/Groups.vue';
import MoneyRecievers from './clinic/MoneyRecievers.vue';
import Departments from './clinic/Departments.vue';

export default {
    components: {
        Clinics,
        ClinicGroups,
        MoneyRecievers,
        Departments,
    },
    data() {
        return {
            activeTab: 'clinics',
        }
    },
}
</script>

