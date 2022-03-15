<template>
    <div class="sections-wrapper time-sheet-wrapper">
        <daysheet-notification :notifications="notifications" />
        <form v-loading="loading">
            <form-date
                :entity="model"
                type="dates"
                format="dd-MM-yyyy"
                property="dates"
                class="pt-20 pl-20 pr-20 mb-0"
                :label="__('Задайте рабочее время сразу на несколько дней')" >
            </form-date>
            <el-row
                v-for="(item, index) in model.time_sheets"
                :key="index"
                class="time-sheet-item p-20" >
                <el-col :span="24">
                    <form-row
                        name="period"
                        :label="__('Время приема')">
                        <template slot="label-addon">
                            <span class="color-danger cursor-pointer" @click="removeTimeSheet(index)">{{ __('Удалить период') }}</span>
                        </template>
                        <el-row>
                            <el-col :span="12">
                                <form-time
                                    :entity="item"
                                    property="time_from"
                                    :error-prefix="`time_sheets.e${index}.`"
                                    :picker-options="{
                                        start: '00:00',
                                        step: '00:05',
                                        end: '23:55',
                                        minTime: minTime(item, index)
                                    }"
                                    @changed="validateTime({item: item, oldVal: item.time_from, field: 'time_from'}, ...arguments)" />
                            </el-col>
                            <el-col :span="12">
                                <form-time
                                    :entity="item"
                                    property="time_to"
                                    :disabled="!item.time_from"
                                    :error-prefix="`time_sheets.e${index}.`"
                                    :picker-options="{
                                        start: '00:00',
                                        step: '00:05',
                                        end: '23:55',
                                        minTime: item.time_from
                                    }"
                                    @changed="validateTime({item: item, oldVal: item.time_to, field: 'time_to'}, ...arguments)" />
                            </el-col>
                        </el-row>
                    </form-row>
                    <form-row
                        name="specializations"
                        :label="__('Выберите специализацию')"
                        class="time-sheet-specializations mb-0"
                        :error-prefix="`time_sheets.e${index}.`" >
                        <el-checkbox-group v-model="item.specializations" >
                            <span
                                v-for="(specialization, index) in specialization_list"
                                :key="index"
                                class="time-sheet-specialization">
                                <el-row :gutter="24" class="mb-0">
                                    <el-col :span="12">
                                        <el-checkbox
                                            :label="specialization.id"
                                            :key="specialization.id" >
                                            {{ specialization.value }}
                                        </el-checkbox>
                                    </el-col>
                                </el-row>
                            </span>
                        </el-checkbox-group>
                    </form-row>
                </el-col>
            </el-row>
            <el-row class="p-20 bb-grey-sh3 add-sheet-column">
                <el-col :span="16">
                    <form-select
                        :entity="model"
                        :repository="doctors"
                        property="doctor_id"
                        :clearable="true"
                        :filterable="true"
                        collapse-tags
                        :label="__('Врач')"
                    />
                </el-col>
                <el-col :span="8" class="text-right">
                    <el-button @click="addTimeSheet" >
                        {{ __('Добавить период') }}
                    </el-button>
                </el-col>
            </el-row>
            <el-row class="form-input text-center dialog-footer">
                <el-col :span="8">
                    <el-button @click="deleteModel">
                        {{ __('Удалить табель') }}
                    </el-button>
                </el-col>
                <el-col :span="8">
                    <el-button @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                </el-col>
                <el-col :span="8">
                    <el-button
                        type="primary"
                        :disabled="emptyTimeSheets"
                        @click="updateModel">
                        {{ __('Задать время') }}
                    </el-button>
                </el-col>
            </el-row>
        </form>
    </div>
</template>

<script>
import EditMixin from './mixin/edit-daysheet';
import SpecializationRepository from '@/repositories/specialization';
import WorkspaceRepository from '@/repositories/workspace';
import TimeSheet from '@/models/day-sheet/time-sheet';
import CONSTANTS from '@/constants';
import EmployeeRepository from '@/repositories/employee';
import DaysheetNotification from './Notification.vue';

export default {
	mixins: [
		EditMixin,
	],
    components: {
        DaysheetNotification,
    },
	mounted() {
        this.$watch('model.dates',  (val, prevVal) => {
            this.getWorkspaceDaySheets();
        });
    },
    data() {
	    return {
            doctors: new EmployeeRepository({
                filters: this.getDoctorFilters(),
            }),
        }
    },
	methods: {
        getDoctorFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                clinic: this.clinic_id,
            });
        },
		getDaySheetData() {
            this.getSpecializations().then(() => this.getWorkspaceDaySheets());
        },
        getSpecializations() {
        	let specialization = new SpecializationRepository();
            return specialization.fetchList(this.getSpecializationFilter()).then((response) => {
                this.specialization_list = response;
                return Promise.resolve();
            });
        },
        getSpecializationFilter() {
            return {
                workspace_clinic: {
                    workspace: this.model.day_sheet_owner_id,
                    clinic: this.clinic_id
                },
            };
        },
        getWorkspaceDaySheets() {
            this.loading = true;
        	let workspace = new WorkspaceRepository();
            let filters = {
                id: this.model.day_sheet_owner_id,
                dates: this.model.dates,
            };

            workspace.fetchDaySheets(filters).then((response) => {
                this.manageDaySheets(response);
                this.setAppointments(response);
                this.loading = false;
            });
        },
        castSpecializationData(item) {
            this.specialization_list.forEach((specialization) => {
                if (_.isNil(item.specialization_data[specialization.id])) {
                    item.specialization_data[specialization.id] = { workspace_id: null };
                }
            });
        },
        getMissedAppointment() {
            let result = {};

            this.appointments.forEach((item) => {
                let match_time_sheet = this.findMatchTimeSheet(item);
                if (!match_time_sheet) {
                    return;
                }

                if (this.appointmentOutOfSpecializations(item, match_time_sheet)) {
                    result = item;
                    return;
                }
                return;
            });
            return result;
        },
        updateModel() {
            let invalidAppointment = this.getMissedAppointment();
            if(!_.isEmpty(invalidAppointment)) {
                return this.$error(this.getSpecializationError(invalidAppointment));
            }
            return this.update();
        },
	}
}
</script>
