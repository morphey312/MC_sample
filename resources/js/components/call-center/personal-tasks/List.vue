<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template
            slot="status"
            slot-scope="props">
            <div 
                v-if="isAssignedTask(props.rowData)"
                class="has-icon">
                <span class="ellipsis">
                    <a 
                        href="#"
                        @click.prevent="handleTask(props.rowData)">
                        {{ $formatter.fromHandbook('personal_task_status', props.rowData.status) }}
                    </a>
                </span>
                <template v-if="wasUpdated(props.rowData)">
                    <svg-icon
                        :title="__('Эта задача была отредактирована {date}', {date: $formatter.datetimeFormat(props.rowData.modified_at)})"
                        name="info-alt" 
                        class="icon-tiny icon-grey" />
                </template>
            </div>
            <template v-else>
                {{ $formatter.fromHandbook('personal_task_status', props.rowData.status) }}
            </template>
        </template>
    </manage-table>
</template>


<script>
import PersonalTaskRepository from '@/repositories/personal-task';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS  from '@/constants';

export default {
    data() {
        return {
            repository: new PersonalTaskRepository(),
            fields: [
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус'),
                    width: '10%',
                    filter: 'personal_task_status',
                },
                {
                    name: 'date',
                    sortField: 'date',
                    title: __('Крайний срок'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                ...(this.$canAccess('personal-tasks') ? [{
                    name: 'operator_name',
                    sortField: 'operator_name',
                    filterField: 'operator',
                    title: __('Оператор'),
                    width: '12%',
                    filter: new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR}}),
                }] : []),
                {
                    name: 'employee_name',
                    sortField: 'employee_name',
                    filterField: 'employee',
                    title: __('Постановщик'),
                    width: '12%',
                    filter: new EmployeeRepository(),
                },
                {
                    name: 'comment',
                    title: __('Задание'),
                    width: '24%',
                    filter: true,
                },
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '12%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('personal-tasks'),
                    }),
                },
                ...(this.$canAccess('personal-tasks') ? [{
                    name: 'outcome',
                    title: __('Обратная связь'),
                    width: '18%',
                    filter: true,
                }] : []),
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            filters: {},
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        handleTask(task) {
            this.$emit('handle-task', task);
        },
        isAssignedTask(task) {
            return task.operator_id == this.$store.state.user.employee_id;
        },
        wasUpdated(task) {
            return task.modified_at !== null
                && task.status_changed_at !== null
                && this.$moment(task.modified_at).isAfter(task.status_changed_at);
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
};
</script>