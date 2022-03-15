<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :flex-height="true"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :filters="filters">
        <template
            slot="employee_name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    <a href="#" @click.prevent="selected(props.rowData)">
                        {{ props.rowData.full_name }}    
                    </a>
                </span>
                <svg-icon
                    v-if="$can('action-logs.access')"
                    :title="__('Операции')"
                    name="info-alt" 
                    class="icon-tiny icon-grey"
                    @click="showLog(props.rowData.id)" />
            </div>
        </template>
    </manage-table>
</template>

<script>
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';
import DaysheetLog from '@/components/action-log/DaySheet.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new EmployeeRepository({
                limitClinics: this.$isAccessLimited('day-sheets'),
            }),
            fields: [
                {
                    name: 'employee_name',
                    sortField: 'fullName',
                    title: __('ФИО'),
                    width: '20%',
                },
                {
                    name: 'clinic_names',
                    title: __('Клиника'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    }
                },
                {
                    name: 'position_names',
                    title: __('Должность'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    }
                },
                {
                    name: 'status_names',
                    title: __('Статус'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.fromHandbook('employee_status', value);
                    }
                },
            ],
            initialSortOrder: [
                {field: 'fullName', direction: 'asc'},
            ],
            scopes: [
                'clinics',
                'clinics.position',
                'clinics.specializations',
                'clinics.clinic'
            ]
        };
    },
    methods: {
        selected(employee) {
            this.$router.push({
                name: 'day-sheet-schedule',
                params: {
                    id: employee.id,
                    owner_type: CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE,
                },
            });
        },
        showLog(id) {
            this.$modalComponent(DaysheetLog, {
                id,
                category: CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения табеля сотрудника'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>
