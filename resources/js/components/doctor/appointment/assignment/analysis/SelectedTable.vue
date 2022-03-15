<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="updateList" >
        <template
            slot="quantity"
            slot-scope="props" >
            <el-input-number
                v-model="props.rowData.quantity"
                :disabled="readonly || !isDeleteable(props.rowData)"
                controls-position="right"
                :step="1"
                :min="1"
                class="input-tiny text-right"
                @change="costChanged(props.rowData)" />
        </template>
        <template
            slot="discount"
            slot-scope="props" >
            <el-input-number
                v-model="props.rowData.discount"
                controls-position="right"
                :step="0.01"
                :disabled="readonly || !isDeleteable(props.rowData) || props.rowData.by_policy == true"
                :max="100"
                :min="0"
                class="input-tiny text-right"
                @change="costChanged(props.rowData)" />
        </template>
        <template slot="by_policy" slot-scope="props">
            <el-checkbox
                v-model="props.rowData.by_policy"
                @change="setAnalysisPrice(props.rowData)" />
        </template>
        <template slot="franchise" slot-scope="props">
            <el-input-number
                v-model="props.rowData.franchise"
                controls-position="right"
                :max="100"
                :min="0"
                :step="0.01"
                class="text-right input-tiny" />
        </template>
        <template slot="warranter" slot-scope="props">
            <el-input v-model="props.rowData.warranter" />
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <template v-if="isDeleteable(props.rowData)">
                <span class="" @click="toggleSelection(props.rowData, props.rowIndex)">
                    <svg-icon name="delete" class="icon-small icon-blue" />
                </span>
            </template>
        </template>
        <template
            slot="date_expected_pass"
            slot-scope="props" >
            <el-date-picker
                v-model="props.rowData.date_expected_pass"
                type="date"
                value-format="yyyy-MM-dd"
                :picker-options="pickerOptions">
            </el-date-picker>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import TableFilter from '@/mixins/appointment/analysis/static-table-filter';
import CONSTANTS from '@/constants';
import AppointmentAnalysisMixin from '@/components/doctor/appointment/mixins/appointment-analysis-table';

export default {
    mixins: [
        AppointmentAnalysisMixin,
        TableFilter
    ],
    props: {
        rows: {
            type: Array,
            default: () => []
        },
        readonly: Boolean,
        model: Object,
        insurancePolicy: {
            type: Object,
            default: null,
        },
        filters: {
            type: Object,
            default: null,
        },
        appointmentData: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.rows
                });
            }),
            fields: [
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: "118px",
                    filter: true,
                    filterField: 'laboratory_code',
                },
                {
                    name: 'analysis.laboratory_name',
                    title: __('Название лаборатории'),
                    width: "100px",
                    filter: true,
                    filterField: 'laboratory_name',
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: "88px",
                    filter: true,
                    filterField: 'clinic_code',
                },
                {
                    name: 'analysis.name',
                    title: __('Название'),
                    filter: true,
                    width: "90px",
                    filterField: 'name',
                },
                {
                    name: 'analysis.clinic.duration_days',
                    title: __('Кол-во дней на анализ'),
                    width: "90px",
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: "80px",
                },
                {
                    name: 'quantity',
                    title: __('Кол-во'),
                    width: "70px",
                },
                {
                    name: 'discount',
                    title: __('Скидка'),
                    width: "60px",
                },
                {
                    name: 'date_expected_pass',
                    title: __('Реком. Дата сдачи'),
                    width: "110px",
                },
                ...(this.insurancePolicy ? [{
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '48px',
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '48px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'warranter',
                    title: __('Гарант'),
                    width: '70px',
                },
                ] : []),
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                }]),
            ],
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            },
        }
    },
    methods: {
        refresh() {
            this.$refs.table.refresh();
        },
        isDeleteable(row) {
            if (_.isVoid(row.appointment_id)) {
                return row.status === CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED || this.readonly;
            }
            return false;
        },
    }
}
</script>
