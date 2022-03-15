<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
            <div class="form-input-group">
            <el-row  :gutter="20">
                        <el-col :span="6">
                            <form-select
                            :entity="filter"
                            :clearable="true"
                            :options="clinic"
                            :multiple="true"
                            :filterable="true"
                            property="clinic"
                            :label="__('Клиника')"/>
                        </el-col>
                        <el-col :span="6">
                            <form-row
                                name="daysheet.date"
                                    :label="__('Дата блока')">
                            <div class="form-input-group">
                                <form-date
                                    :entity="filter"
                                    property="blocked_from"
                                    :editable="false"
                                    :clearable="true"
                                />

                                <form-date
                                    :entity="filter"
                                    property="blocked_to"
                                    :editable="false"
                                    :clearable="true"
                                />
                            </div>
                             </form-row>
                        </el-col>

                        <el-col :span="6">
                            <form-row
                                name="created_at"
                                    :label="__('Дата создания')">
                            <div class="form-input-group">
                                <form-date
                                    :entity="filter"
                                    property="date_from"
                                    :editable="false"
                                    :clearable="true"
                                />

                                <form-date
                                    :entity="filter"
                                    property="date_to"
                                    :editable="false"
                                    :clearable="true"
                                />
                            </div>
                             </form-row>
                        </el-col>



            </el-row>
            </div>
            <div class="form-input-group">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filter"
                        :options="status_list"
                        :clearable="true"
                        property="status"
                        :label="__('Статус блокировки')"
                    />
                </el-col>
                <el-col :span="6">
                    <form-select
                        :entity="filter"
                        :repository="doctor"
                        property="doctor"
                        :clearable="true"
                        :label="__('Врач')"
                    />
                </el-col>
                <el-col :span="6">
                <form-select
                    :entity="filter"
                    :repository="employee"
                    property="employee"
                    :clearable="true"
                    :label="__('Оператор')"
                />
                </el-col>


            </el-row>
            </div>

        <div class="form-input-group">
            <el-row :gutter="20">
                <el-col :span="6">
            <form-select
                :entity="filter"
                :options="blockReasons"
                :multiple="true"
                :clearable="true"
                property="reason"
                :filterable="true"
                :label="__('Причина блокировки')" />
                </el-col>
            <el-col :span="6">
            <form-select
                :entity="filter"
                :options="reasonUnblock"
                :multiple="true"
                property="reason_off"
                :clearable="true"
                :filterable="true"
                :label="__('Причина разблокировки')" />
                </el-col>
            <el-col :span="6">

            </el-col>
                <el-col :span="6">
                    <form-select
                        :clearable="true"
                        :entity="filter"
                        :repository="employee"
                        property="employee_off"
                        :label="__('Оператор снявший блок')"
                    />
                </el-col>
            </el-row>

        </div>


    </search-filter>
</template>

<script>
import LockLogRepository from '@/repositories/locklog';

import SpecializationRepository from '@/repositories/specialization';
import DaySheetTimeBlockReasonRepository from '@/repositories/day-sheet/time-block-reason';
import ReasonUnblockRepository from '@/repositories/reason-unblock';

import EmployeeRepository from '@/repositories/employee';
import ClinicRepository from '@/repositories/clinic';
import FilterMixin from '@/mixins/filter';
import Grid from '@/components/appointments/mixin/grid';
import CONSTANTS from '@/constants';
export default {
    mixins: [
        FilterMixin,
        Grid
    ],
    data() {
        return {
            repository: new LockLogRepository(),
            specializations: new SpecializationRepository(),
            employee: new EmployeeRepository(),
            clinic: new ClinicRepository({
                accessLimit: !this.$can('lock-log.access'),
            }),
            reasonUnblock: new ReasonUnblockRepository(),
            doctor: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            }),
            blockReasons: new DaySheetTimeBlockReasonRepository(),
            status_list: [
                {
                    id: null,
                    value: __('все'),
                },
                {
                    id: '1',
                    value: __('активна'),
                },
                {
                    id: '0',
                    value: __('снята'),
                }
            ],
            operators: [
                {
                    id: this.$store.state.user.employee_id,
                    value: this.$store.state.user.employee.full_name,
                }
            ],
        };
    },
    computed: {
        timeOptions(){
           let start = CONSTANTS.DAY_SHEET.GRID_PERIOD.START;
            let end = CONSTANTS.DAY_SHEET.GRID_PERIOD.END;

            this.createTimeList({start,end });
            return this.timeRange.map((value, key) => {
                return {
                    id: value,
                    value: value
                }
            })
        }
    },
    methods: {
        initFilter(fromState = {}) {
            let today = this.$moment().format('YYYY-MM-DD');
            this.filter = {
                status: null,
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.appointment.clinic,
            });
        },
    },
    watch: {
        ['filter.appointment.clinic']() {
            this.specializations.setFilters(this.getSpecializationFilters());
        },
    },
};
</script>
