<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-select
                            :entity="model"
                            :options="clinics"
                            property="clinic_id"
                            :filterable="true"
                            :label="__('Клиника')"
                        />
                        <form-input
                            :entity="model"
                            property="sip_number"
                            :label="__('Номер SIP')"
                        />
                        <form-date
                            :entity="model"
                            property="date_start_working"
                            :label="__('Дата начала работы')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="is_primary"
                            :label="__('Основная клиника')"
                        />
                    </el-col>
                    <el-col :span="8">
                        <form-select
                            :entity="model"
                            options="employee_status"
                            property="status"
                            :label="__('Статус')"
                        />
                        <form-select
                            :entity="model"
                            :options="positions"
                            property="position_id"
                            :label="__('Должность')"
                        />
                        <form-date
                            :entity="model"
                            property="date_end_working"
                            :label="__('Дата окончания работы')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="can_recieve_transfer"
                            :label="__('Может получать межкассовые переводы')"
                        />
                    </el-col>
                    <el-col :span="8">
                        <form-input
                            v-if="selectedPosition && selectedPosition.is_doctor"
                            key="doctor"
                            :entity="model"
                            property="doctor.appointment_duration"
                            :label="__('Длительность первичного приема, мин.')"
                            type="number"
                            :step="5"
                            :min="5"
                        />
                        <form-input
                            v-if="selectedPosition && selectedPosition.has_voip"
                            key="operator"
                            :entity="model"
                            type="password"
                            property="sip_password"
                            :label="__('Пароль для телефонии')"
                        />
                        <form-select
                            v-if="selectedPosition && selectedPosition.is_operator"
                            :entity="model"
                            options="enquiry_categories"
                            :multiple="true"
                            property="enquiry_categories"
                            :label="__('Доступные типы заявок')"
                        />
                        <form-input
                            v-if="selectedPosition && selectedPosition.is_doctor"
                            :entity="model"
                            property="doctor.appointment_duration_repeated"
                            type="number"
                            :label="__('Длительность повторного приема, мин.')"
                            :step="5"
                            :min="5"
                        />
                    </el-col>
                </el-row>
            </section>
            <template v-if="selectedPosition && selectedPosition.has_specialization">
                <hr />
                <section>
                    <form-row name="specializations">
                        <transfer-table
                            key="specializations"
                            v-model="model.specializations"
                            :items="specializations"
                            :fields="specializationFields"
                            value-key="specialization_id"
                            :left-title="__('Специализация')"
                            left-width="225px"
                            :right-title="__('Специализация')"
                            right-width="225px"
                            @new-row="initSpecialization"
                        >
                            <template
                                slot="workspace_id"
                                slot-scope="props"
                            >
                                <form-select
                                    :entity="props.rowData.data"
                                    :options="workspaces"
                                    :clearable="true"
                                    property="workspace_id"
                                    control-size="mini"
                                    css-class="table-row"
                                />
                            </template>
                            <template
                                slot="priority"
                                slot-scope="props"
                            >
                                <form-select
                                    :entity="props.rowData.data"
                                    options="specialization_priority"
                                    property="priority"
                                    css-class="table-row"
                                    control-size="mini"
                                    @changed="toggleSpecializationPriority(props.rowData.data, $event)"
                                />
                            </template>
                        </transfer-table>
                    </form-row>
                </section>
            </template>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import SpecializationRepository from '@/repositories/specialization';
import WorkspaceRepository from '@/repositories/workspace';

export default {
    props: {
        model: {
            type: Object
        },
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
                filters: this.getClinicFilters()
            }),
            positions: [],
            specializations: [],
            workspaces: [],
            selectedPosition: null,
            specializationFields: [
                {
                    name: 'workspace_id',
                    title: __('Кабинет (опционально)'),
                    width: '150px',
                },
                {
                    name: 'priority',
                    title: __('Приоритет специализации'),
                    width: '150px',
                },
            ],
        };
    },
    watch: {
        ['model.position_id'](val) {
            this.setActivePosition(val);
        },
        ['model.clinic_id'](val) {
            this.getSpecializations(val);
            this.getWorkspaces(val);
            this.clinics.setFilter(this.getClinicFilters());
        },
    },
    mounted() {
        this.getPositions();
        this.getSpecializations(this.model.clinic_id);
        this.getWorkspaces(this.model.clinic_id);
    },
    methods: {
        setActivePosition(val) {
            this.model.position = _.find(this.positions, (item) => {
                return item.id === val;
            });
            this.selectedPosition = this.model.position;
        },
        getPositions() {
            let position = new PositionRepository();
            position.fetchList().then((response) => {
                this.positions = response;
                this.setActivePosition(this.model.position_id);
            });
        },
        getWorkspaces(clinic) {
            this.workspaces = [];
            if (clinic) {
                let workspace = new WorkspaceRepository();
                workspace.fetchList({clinic, hasDaySheet: 0}).then((response) => {
                    this.workspaces = response;
                });
            }
        },
        getSpecializations(clinic) {
            this.specializations = [];
            if (clinic) {
                let specialization = new SpecializationRepository();
                specialization.fetchList({active_clinic: clinic}).then((response) => {
                    this.specializations = response;
                });
            }
        },
        initSpecialization(data) {
            if (this.model.specializations.length === 0) {
                data.priority = 1;
            } else {
                data.priority = 0;
            }
        },
        toggleSpecializationPriority(row, value) {
            if (value == 1) {
                this.model.specializations.forEach((spec) => {
                    if (row.specialization_id != spec.specialization_id) {
                        spec.priority = 0;
                    }
                });
            }
        },
        getClinicFilters() {
            if (this.model.clinic_id) {
                return _.onlyFilled({
                    not_in_employee_clinics: [this.model.employee_id, this.model.clinic_id]
                })
            }
            return _.onlyFilled({
                not_in_employee_clinics: [this.model.employee_id]
            })
        }
    },
}
</script>
