<template>
    <div class="sections-wrapper">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-select
                        :entity="doctorTimeLock"
                        :options="clinics"
                        property="clinic"
                        :disabled="true"
                        :filterable="true"
                        :label="__('Клиника')"
                    />
                    <form-select
                        :entity="doctorTimeLock"
                        :options="employees"
                        property="doctor"
                        :clearable="true"
                        :filterable="true"
                        :disabled="true"
                        :label="__('Врач')" />
                    <form-select
                        :entity="doctorTimeLock"
                        :options="specializations"
                        :disabled="true"
                        property="specializations"
                        :multiple="true"
                        :label="__('Специализация')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-select
                        :entity="doctorTimeLock"
                        :options="blockReasons"
                        :disabled="watching !== false"
                        property="reason"
                        :filterable="true"
                        :label="__('Причина блокировки')" />
                    <form-date
                        :entity="doctorTimeLock"
                        property="date"
                        :disabled="true"
                        :label="__('Дата блока')" />
                    <form-row
                        name="dates"
                        css-class="form-input-group"
                        label="" >
                        <form-select
                            :entity="doctorTimeLock"
                            :options="timeOptions"
                            property="start"
                            :disabled="watching !== false"
                            :filterable="true"
                            :label="__('Начало блока')" />
                        <div class="form-input">
                            <div class="label-wrapper">
                                <label class="input-label">{{ __('Длительность') }}</label>
                            </div>
                            <el-select
                                :disabled="watching !== false"
                                v-model="doctorTimeLock.duration"
                            >
                                <el-option
                                    v-for="item in minuteList"
                                    :key="item"
                                    :value="item"
                                />
                            </el-select>
                        </div>
                    </form-row>
                </el-col>
                <el-col :span="8">
                    <form-select
                        :entity="doctorTimeLock"
                        :repository="operators"
                        :disabled="true"
                        property="creator"
                        :label="__('Оператор')"
                    />
                    <form-text
                        :entity="doctorTimeLock"
                        property="comment"
                        :disabled="watching !== false"
                        :rows="2"
                        class="three-rows-height"
                        :label="__('Комментарий')" />

                    <form-date
                        v-if="watching !== false"
                        :entity="doctorTimeLock"
                        property="created_at"
                        :disabled="true"
                        :label="__('Дата создания')" />
                </el-col>
            </el-row>
            <div
                class="form-footer text-right">
                <el-button
                    v-if="watching === false"
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    v-if="watching === false"
                    @click="block">
                    {{ __('Блокировать') }}
                </el-button>
            </div>
        </section>
    </div>
</template>
<script>
import ManageMixin from '@/mixins/manage';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import SpecializationRepository from '@/repositories/specialization';
import DaySheetTimeBlockReasonRepository from '@/repositories/day-sheet/time-block-reason';
import CONSTANTS from '@/constants';
import MESSAGES from '@/messages';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        time: String,
        timeRange: {
            type: Array,
            default: () => []
        },
        watching: {
            default: false
        },
        column: {
            type: Object
        },
        sheetSpecializations: {
            type: Array,
            default: () => []
        },
    },
    data() {
        return {
            model: null,
            clinics: new ClinicRepository(),
            employees: [
                {
                    id: this.column.doctor.id,
                    value: this.column.doctor.full_name,
                }
            ],
            operators: new EmployeeRepository(),
            specializations: new SpecializationRepository(),
            blockReasons: new DaySheetTimeBlockReasonRepository(),
            minuteList: [],
            doctorTimeLock: {}
        };
    },
    computed: {
        timeOptions(){
            return this.timeRange.map((value, key) => {
                return {
                    id: value,
                    value: value
                }
            })
        }
    },
    mounted() {
        let reason = null;
        let comment = null;
        let creator = this.$store.state.user.employee_id;
        let created_at = this.$moment().format('YYYY-MM-DD');
        if(this.watching !== false){
            if(this.column.locks[this.watching]){
                reason = (typeof this.column.locks[this.watching].reason == 'undefined' ? this.column.locks[this.watching].reason_id : this.column.locks[this.watching].reason.id);
                comment = this.column.locks[this.watching].comment;
                creator = this.column.locks[this.watching].employee_id;
                if(this.column.locks[this.watching].created_at){
                    created_at = this.column.locks[this.watching].created_at ;
                }

            }
        }

        this.doctorTimeLock = {
            clinic: this.column.clinic_id,
            doctor: this.column.doctor.id,
            specializations: this.sheetSpecializations.map((specialization)=> {
                return parseInt(specialization.id)
            }),
            date: this.column.date,
            reason: reason,
            duration: 20,
            creator: creator,
            comment: comment,
            start: this.time,
            created_at: created_at,
            end: null,
            watching: this.watching
        }

        this.makeMinutesList();
    },
    methods: {
        cancel() {
            this.$emit('close');
        },
        block(){
            if(this.validateBlock()) {
                this.$emit('block', this.doctorTimeLock);
            }
        },
        validateBlock(){
            if(this.doctorTimeLock.reason === null){
                this.$error(__('Выберите причину  блокировки'))
                return false;
            }

            let momentedStart = this.$moment(`${this.doctorTimeLock.date} ${this.getTimeString(this.doctorTimeLock.start)}`);
            let momentedEnd = momentedStart.clone().add(this.doctorTimeLock.duration,'minutes');

            return this.isInTimeSheets(momentedStart, momentedEnd)
                && !this.isOverlapping(momentedStart, momentedEnd)
                && !this.isAppointmentOverlapping(momentedStart, momentedEnd);


        },

        isInTimeSheets(start, end) {
            let validEnd = false;

            _.each(this.column.time_sheets, (timeSheet) => {
                let from = `${this.doctorTimeLock.date} ${timeSheet.time_from}`;
                let to   = `${this.doctorTimeLock.date} ${timeSheet.time_to}`;

                if(start.isBetween(from, to,null, '[]') && end.isBetween(from, to, null, '[]')){
                    validEnd = true;
                }
            });

            if(!validEnd){
                this.$error(MESSAGES.ERROR.DOCTOR_LOCK_OUT_OF_TIME);
            }

            return validEnd;
        },
        isOverlapping(start, end) {
            let overlapping = false;


            let crossMatch = this.column.locks.find((item) => {
                return item.momented.start.isBetween(start, end) ||
                    item.momented.end.isBetween(start, end);
            });

            if(!_.isNil(crossMatch)){
                overlapping = true;
                this.$error(MESSAGES.ERROR.DOCTOR_LOCK_OVERLAP);

            }

            return overlapping;
        },
        isAppointmentOverlapping(start, end) {
            let overlapping = false;
            let crossMatch = this.column.appointments.find((item) => {
                return item.momented.start.isBetween(start, end) ||
                    item.momented.end.isBetween(start, end);
            });

            if(!_.isNil(crossMatch)){
                overlapping = true;
                this.$error(MESSAGES.ERROR.DOCTOR_LOCK_APPOINTMENT_OVERLAP);
            }

            return overlapping;
        },
        makeMinutesList() {
            let minMinutes = CONSTANTS.SCHEDULE_TIME_STEP;
            let maxMinutes = 1435;
            this.minuteList = [];

            do {
                this.minuteList.push(minMinutes);
                minMinutes += CONSTANTS.SCHEDULE_TIME_STEP;
            } while (minMinutes <= maxMinutes)
        },
        getTimeString(time) {
            if (time.length === 5) {
                time += ":00";
            }
            return time;
        },
    }
}
</script>
