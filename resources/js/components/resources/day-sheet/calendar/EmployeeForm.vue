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
                class="time-sheet-item p-20">
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
                        :label="__('Выберите специализацию и кабинет')"
                        class="time-sheet-specializations mb-0"
                        :error-prefix="`time_sheets.e${index}.`" >
                        <el-checkbox-group v-model="item.specializations" >
                            <span 
                                v-for="(specialization, index) in specialization_list" 
                                class="time-sheet-specialization" 
                                :key="index">
                                <el-row :gutter="24" class="mb-0">
                                    <el-col :span="12">
                                        <el-checkbox
                                            :label="specialization.id"
                                            :key="specialization.id" >
                                            {{ specialization.value }}
                                        </el-checkbox>
                                    </el-col>
                                    <el-col :span="12" v-if="item.specialization_data[specialization.id]">
                                        <el-select 
                                            v-model="item.specialization_data[specialization.id].workspace_id" 
                                            :clearable="true">
                                            <el-option
                                                v-for="workspace in workspace_list"
                                                :key="workspace.id"
                                                :label="workspace.value"
                                                :value="workspace.id">
                                            </el-option>
                                        </el-select>
                                    </el-col>
                                </el-row>
                            </span>
                        </el-checkbox-group>
                    </form-row>
                </el-col>
            </el-row>
            <el-row class="p-20 bb-grey-sh3 text-right">
                <el-button @click="addTimeSheet">
                    {{ __('Добавить период') }}
                </el-button>
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
import EmployeeRepository from '@/repositories/employee';
import WorkspaceRepository from '@/repositories/workspace';
import TimeSheet from '@/models/day-sheet/time-sheet';
import Employee from '@/models/employee';
import DaysheetNotification from './Notification.vue';

export default {
	mixins: [
		EditMixin,
	],
    components: {
        DaysheetNotification,
    },
	data() {
		return {
			workspace_list: [],
            errorWorkspaces: __('Вы выбрали больше чем 1 кабинет. Укажите один'),
		};
	},
	mounted() {
        this.$watch('model.dates',  (val, prevVal) => {
            if(val.length != 0) {
                this.getEmployeeDaySheets();    
            }
        });
    },
	methods: {
		getDaySheetData() {
            this.getWorkspaces();
            this.getSpecializations().then(() => this.getEmployeeDaySheets());
        },
        getWorkspaces() {
            let workspace = new WorkspaceRepository();
            workspace.fetchList({clinic: this.clinic_id, hasDaySheet: 0}).then((response) => {
                this.workspace_list = response;
            });
        },
        castSpecializationData(item) {
            this.specialization_list.forEach((specialization) => {
                if(_.isNil(item.specialization_data[specialization.id])) {
                    item.specialization_data[specialization.id] = { workspace_id: specialization.workspace_id };
                }
            });
        },
        getSpecializations() {
            let employee = new Employee({id: this.model.day_sheet_owner_id});

            return employee.fetch(['clinics.specializations']).then(() => {
                let clinic = _.find(employee.employee_clinics, {clinic_id: this.clinic_id});

                if (clinic) {
                    this.specialization_list =  clinic.specializations.map((specialization) => {
                        specialization.id = specialization.specialization_id;
                        specialization.value = specialization.name;

                        return specialization;
                    }).filter((specialization) => specialization.status === 1)
                }
                return Promise.resolve();
            });
        },
        getEmployeeDaySheets() {
            this.loading = true;
            let employee = new EmployeeRepository();
            let filters = {
                id: this.model.day_sheet_owner_id,
                dates: this.model.dates,
            };

            employee.fetchDaySheets(filters).then((response) => {
                this.day_sheets = response;
                this.manageDaySheets(employee.castEmployeeTimeSheets(response, 'date'));
                this.setAppointments(response);
                this.loading = false;
            });
        },
        hasDifferentWorkspaces() {
            let chosenWorkspaces = [];

            this.model.time_sheets.forEach((sheet) => {
                sheet.specializations.forEach((id) => {
                    let workspace = sheet.specialization_data[id].workspace_id;

                    if (workspace && chosenWorkspaces.indexOf(workspace) === -1) {
                        chosenWorkspaces.push(workspace);
                    }
                });
            });

            return chosenWorkspaces.length > 1;
        },
        getMissedAppointment() {
            let result = {};

            this.appointments.forEach((item) => {
                let match_time_sheet = this.findMatchTimeSheet(item);
                if (!match_time_sheet) {
                    return;
                }

                if (this.appointmentOutOfSpecializations(item, match_time_sheet)) {
                    result.specialization = item;
                    return;
                }

                let inPeriod = this.findWokrspacedTimeSheet(item, match_time_sheet);
            
                if (!inPeriod) {
                    result.appointment = item;
                    return;
                }
                return;
            });

            return result;
        },
        findWokrspacedTimeSheet(item, time_sheet) {
            return  _.find(time_sheet.specializations, (spec_id) => {
                if(_.isFilled(item.workspace_id)) {
                    return item.workspace_id == time_sheet.specialization_data[spec_id].workspace_id;
                }

                return  _.isVoid(time_sheet.specialization_data[spec_id].workspace_id) && 
                        _.isVoid(item.workspace_id);
            });
        },
        getWorkspaceError(appointment) {
            let workspace = _.find(this.workspace_list, {id: appointment.workspace_id});
            return  __('В кабинете есть запись на прием: {period}, {workspace}', {period: this.getAppointmentPeriod(appointment), workspace: (workspace ? workspace.value : '')});
        },
        updateModel() {
            if (this.hasDifferentWorkspaces()) {
                return this.$error(this.errorWorkspaces);
            }

            let invalidAppointment = this.getMissedAppointment();

            if (!_.isEmpty(invalidAppointment)) {
                if (invalidAppointment.specialization) {
                    return this.$error(this.getSpecializationError(invalidAppointment.specialization));
                }

                if (invalidAppointment.appointment) { 
                    return this.$error(this.getWorkspaceError(invalidAppointment.appointment));
                }
            }
            return this.update();
        }
	}
}	
</script>