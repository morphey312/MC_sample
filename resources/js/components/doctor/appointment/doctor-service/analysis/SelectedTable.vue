<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="updateList" >
        <template
            slot="date_pass"
            slot-scope="props" >
            <div class="price-grid">
                <inline-datepicker
                    v-model="props.rowData.date_pass"
                    :max-date="$moment().format('YYYY-MM-DD')" />
            </div>
        </template>
        <template
            slot="quantity"
            slot-scope="props" >
            <el-input-number
                v-model="props.rowData.quantity"
                :disabled="readonly || !isEditable(props.rowData)"
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
                :disabled="readonly || !isEditable(props.rowData) || props.rowData.by_policy == true"
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
            slot="date_expected_ready"
            slot-scope="props" >
            <el-date-picker
                v-model="props.rowData.date_expected_ready"
                type="date"
                value-format="yyyy-MM-dd"
                format="dd MMM yyyy"
                :picker-options="pickerOptions" />
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <span
                v-if="isEditable(props.rowData)"
                @click="toggleSelection(props.rowData, props.rowIndex)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
</template>
<script>
import AppointmentAnalysisMixin from '@/components/doctor/appointment/mixins/appointment-analysis-table';
import ProxyRepository from '@/repositories/proxy-repository';
import TableFilter from '@/mixins/appointment/analysis/static-table-filter';

export default {
    mixins: [
        AppointmentAnalysisMixin,
        TableFilter,
    ],
    props: {
        rows: {
            type: Array,
            default: () => []
        },
        model: Object,
        readonly: Boolean,
        insurancePolicy: {
            type: Object,
            default: null,
        },
        appointmentData: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.filteredResults
                })
            }),
            fields: [
                {
                    name: 'date_pass',
                    title: __('Дата сдачи'),
                    width: "110px",
                },
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
                    width: "155px",
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
                    title: __('Название анализов'),
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во'),
                    width: "70px",
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: "80px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'discount',
                    title: __('Скидка'),
                    width: "60px",
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
                {
                    name: 'date_expected_ready',
                    title: __('Дата предв. готовности'),
                    width: "120px",
                },
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                }]),
            ],
            filteredResults: this.rows,
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            },
        }
    },
    methods: {
        isEditable(row) {
            if (row.payed > 0) {
                return false;
            } else if (!_.isFilled(row.date_pass) || _.isVoid(row.date_pass)) {
                return true;
            }
        },
    }
}
</script>
