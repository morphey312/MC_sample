<template>
    <page
        :title="__('Табели сотрудников и кабинетов')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey">
                <day-sheet-filter 
                    ref="search" 
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Сотрудники')"
                name="employees" >
                <section class="pt-0 shrinkable">
                    <employee-list :filters="filters"/>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                v-if="$canAccess('workspaces')"
                :label="__('Кабинеты')"
                name="workspaces" >
                <section class="pt-0 shrinkable">
                    <workspace-list :filters="filters"/>
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import DaySheetFilter from './day-sheet/Filter.vue';
import EmployeeList from './day-sheet/EmployeeList.vue';
import WorkspaceList from './day-sheet/WorkspaceList.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DaySheetFilter,
        EmployeeList,
        WorkspaceList,
    },
    data() {
        return {
            activeTab: 'employees',
        }
    },
    methods: {
        getDefaultFilters() {
            return {
                clinic: this.getLoggedUserClinics(),
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                hasSpecializations: true,
                hasDaySheet: 1,
                is_active: true,
            }
        }
    },
}
</script>
