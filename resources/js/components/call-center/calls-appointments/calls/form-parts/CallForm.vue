<template>
    <el-row :gutter="20">
        <el-col :span="-15">
            <form-date
                :entity="model"
                property="date"
                :label="__('Дата звонка')"
            />
            <form-time
                :entity="model"
                property="time"
                :label="__('Время звонка')"
                mode="picker"
            />
        </el-col>
        <template v-if="isPatient">
            <el-col :span="-15">
                <form-select
                    key="clinic_id"
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')"
                />
                <form-select
                    key="specialization_id"
                    :entity="model"
                    :options="specializations"
                    :required="true"
                    property="specialization_id"
                    :label="__('Врачебная специализация')"
                />
            </el-col>
            <el-col :span="-15">
                <form-select
                    key="employee_id"
                    :entity="model"
                    :repository="operators"
                    property="employee_id"
                    :label="__('Оператор')"
                />
                <form-select
                    key="call_request_id"
                    :entity="model"
                    :options="callRequests"
                    property="call_request_id"
                    :label="__('Заявка на прозвон')"
                    popper-class="select-table">
                    <select-table-header
                        slot="picker-header"
                        :fields="callRequestFields" />
                    <template
                        slot="picker-item"
                        slot-scope="props">
                        <select-table-row
                            :option="props.option"
                            :fields="callRequestFields" />
                    </template>
                </form-select>
            </el-col>
            <el-col :span="-15">
                <form-select
                    key="call_result_id"
                    :entity="model"
                    :options="results"
                    :filterable="true"
                    property="call_result_id"
                    :label="__('Результат звонка')"
                />
                <template v-if="model.waitListRequired">
                    <form-row
                        name="dates"
                        :label="__('Предполагаемый период')"
                        :required="true">
                        <div class="form-input-group">
                            <form-date
                                error-prefix="wait_list_record."
                                :entity="model.wait_list_record"
                                :clearable="true"
                                property="period_from" />
                            <form-date
                                error-prefix="wait_list_record."
                                :entity="model.wait_list_record"
                                :clearable="true"
                                property="period_to"
                                :options="pickerOptions" />
                        </div>
                    </form-row>
                    <form-select
                        :entity="model.wait_list_record"
                        :options="doctors"
                        :filterable="true"
                        property="doctor_id"
                        :label="__('Врач')"
                    />
                </template>
                <form-select
                    v-show="model.waitListReasonRequired"
                    :required="true"
                    :entity="model.wait_list_record"
                    options="wait_list_record_cancel_reason"
                    property="cancel_reason"
                    :label="__('Причина отмены заявки')"
                />
            </el-col>
        </template>
        <template v-else>
            <el-col :span="-15">
                <form-select
                    key="employee_id"
                    :entity="model"
                    :repository="operators"
                    property="employee_id"
                    :label="__('Оператор')"
                />
                <form-select
                    key="call_result_id"
                    :entity="model"
                    :options="results"
                    :filterable="true"
                    property="call_result_id"
                    :label="__('Результат звонка')"
                />
            </el-col>
        </template>
        <el-col :span="-15">
            <form-text
                :entity="model"
                property="comment"
                :label="__('Примечание к звонку')"
            />
        </el-col>
    </el-row>
</template>
<script>
import CONSTANTS from '@/constants';
import SelectTableHeader from '@/components/general/form/select/TableHeader.vue';
import SelectTableRow from '@/components/general/form/select/TableRow.vue';
import EmployeeRepository from '@/repositories/employee';
import WaitListRecord from '@/models/wait-list-record';

export default {
    components: {
        SelectTableHeader,
        SelectTableRow,
    },
    props: {
        model: {
            type: Object,
            required: true
        },
        specializations: {
            type: Object,
            required: true
        },
        operators: {
            type: Object,
            required: true
        },
        clinics: {
            type: Object,
            required: true
        },
        results: {
            type: Object,
            required: true
        },
        callRequests: {
            type: Object,
            required: true
        },
        isPatient: {
            type: Boolean,
        }
    },
    data() {
        return {
            callRequestFields: [
                {
                    name: 'clinic',
                    label: __('Клиника'),
                    width: '100px',
                },
                {
                    name: 'purpose',
                    label: __('Цель прозвона'),
                    width: '150px',
                },
                {
                    name: 'recall',
                    label: __('Период прозвона'),
                    width: '150px',
                    formatter: (value) => {
                        return this.$formatter.daterangeFormat(value);
                    },
                },
                {
                    name: 'specialization',
                    label: __('Специализация'),
                    width: '100px',
                },
                {
                    name: 'doctor',
                    label: __('Врач'),
                    width: '150px',
                },
            ],
            savedWaitListRecord: null,
            doctors: new EmployeeRepository({
                filters: this.getDoctorFilters(),
            }),
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            },
        };
    },
    beforeMount() {
        this.model.set({waitListReasonRequired: false, waitListRequired: false});
        if (!this.model.isNew()) {
            this.savedWaitListRecord = new WaitListRecord(this.model.wait_list_record.attributes);
            this.model.waitListReasonRequired = _.isFilled(this.model.wait_list_record.cancel_reason);
            if (!this.model.waitListReasonRequired) {
                this.model.waitListRequired = this.model.wait_list_record.id != null;
            }
        }
    },
    methods: {
        setwaitListRequired() {
            this.getCallResult().then(result => {
                if (result && result.for_wait_list) {
                    this.model.waitListRequired = true;
                    this.model.waitListReasonRequired = false;

                } else {
                    this.model.waitListRequired = false;
                    this.setWaitListRecordDefaults();
                    this.setShowWaitReason();
                }
            });
        },
        getCallResult() {
            return this.results.fetchList().then(list => {
                let result = list.find(item => item.id == this.model.call_result_id);
                return Promise.resolve(result);
            });
        },
        setShowWaitReason() {
            if (!this.model.isNew() && this.savedWaitListRecord.id != null) {
                this.model.waitListReasonRequired = true;
            }
        },
        getDoctorFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                clinic: this.model.clinic_id,
                specialization: this.model.specialization_id,
                employment_range: {
                    date_start_working: this.model.date,
                    date_employment_end: this.model.date,
                    clinics: [this.model.clinic_id]
                },
            });
        },
        checkDisabledDate(date) {
            let dateFrom = this.model.wait_list_record ? this.model.wait_list_record.period_from : this.$moment();
            return this.$moment(date).isBefore(dateFrom, "day");
        },
        setWaitListRecordDefaults() {
            this.model.wait_list_record = new WaitListRecord({id: (this.savedWaitListRecord ? this.savedWaitListRecord.id : null)});
        },
    },
    watch: {
        ['model.call_result_id'](val) {
            if (!val) {
                this.setWaitListRecordDefaults();
            }
            this.setwaitListRequired();
        },
        ['model.clinic_id']() {
            this.doctors.setFilters(this.getDoctorFilters());
        },
        ['model.specialization_id'](val) {
            this.$emit('spec_changed', val);
            this.doctors.setFilters(this.getDoctorFilters());
        },
        waitListRequired() {
            this.doctors.setFilters(this.getDoctorFilters());
        },
    }
}
</script>
