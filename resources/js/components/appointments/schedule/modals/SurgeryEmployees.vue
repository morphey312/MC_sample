<template>
    <div v-loading="loading">
        <form-row name="doctors" v-if="!loading">
            <transfer-table
                key="doctors"
                :items="employees"
                :items-fields="itemFields"
                :fields="doctorFields"
                v-model="model.surgery_employees"
                value-key="employee_id"
                :left-title="__('Доступные сотрудники')"
                left-width="225px"
                :right-title="__('Выбранные сотрудники')"
                right-width="225px">
                <template slot="role" slot-scope="props">
                    <form-select
                        :entity="props.rowData.data"
                        options="surgery_role"
                        :clearable="true"
                        property="role"
                        control-size="mini"
                        css-class="table-row" />
                </template>
            </transfer-table>
        </form-row>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="save">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import Appointment from '@/models/appointment';
import EmployeeRepository from '@/repositories/employee';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';

export default {
    props: {
        appointment: {
            type: Object,
            default: () => ({}),
        },
        owner: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: true,
            model: new Appointment({id: this.appointment.id, surgery_employees: this.appointment.surgery_employees}),
            employees: [],
            specializations: [],
            itemFields: [],
            doctorFields: [
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '140px',
                    editable: false,
                },
                {
                    name: 'role',
                    title: __('Роль'),
                    width: '100px',
                },
            ],
            isHospitalRoom: this.owner.is_hospital_room === true,
        }
    },
    mounted() {
        this.getEmployees();
    },
    methods: {
        cancel() {
            this.$emit('close');
        },
        save() {
            let allowBlock = !this.isHospitalRoom;
            this.model.saveSurgeryEmployees(this.model.surgery_employees, allowBlock).then(() => {
                this.$info(__('Врачи успешно добавлены'));
                this.$emit('close');
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        getEmployees() {
            this.loading = true;
            let employee = new EmployeeRepository();
            employee.fetchSurgeryList(this.getEmployeeFilters()).then(response => {
                this.prepareTableData(response);
                this.castItemFields();
                this.loading = false;
            });
        },
        getEmployeeFilters() {
            if (this.isHospitalRoom) {
                return {
                    employee_clinic: {
                        clinic: this.appointment.clinic_id,
                        status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    },
                };
            }
            return {
                or: [
                    {
                        date_day_sheet: {
                            date: this.appointment.date,
                            start: this.appointment.start,
                            end: this.appointment.end,
                            clinic_id: this.appointment.clinic_id,
                        },
                    },
                    {
                        employee_clinic: {
                            clinic: this.appointment.clinic_id,
                            status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                            position_type: CONSTANTS.EMPLOYEE.POSITIONS.SURGERY,
                        }
                    }
                ]
            };
        },
        castItemFields() {
            this.itemFields = [
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '140px',
                    editable: false,
                    filter: new SpecializationRepository({filters: this.getSpecializationFilters()}),
                    filterField: 'specializations',
                    filterProps: {
                        multiple: true,
                    },
                },
            ];
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                id: _.uniq(this.specializations),
            });
        },
        prepareTableData(data) {
            this.employees = [];
            data.forEach(item => {
                let employee = {
                    id: item.id, 
                    value: item.full_name
                };
                let clinic = item.employee_clinics.find(item => item.clinic_id === this.appointment.clinic_id);

                if (clinic) {
                    employee.specialization_names = clinic.specialization_names.join(', ');
                    let specializations = clinic.specializations.map(row => row.specialization_id);
                    employee.specializations = specializations;
                    this.specializations = [...this.specializations, ...specializations];
                }
                this.employees.push(employee);
            });
        },
    },
}
</script>