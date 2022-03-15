<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            :max-height="getHeight()"
            header-row-class-name="light-grey"
            :row-class-name="tableRowClassName"
            cell-class-name="text-select">
            <el-table-column
                v-for="(column, index) in columns"
                :key="index"
                fixed
                :prop="column['prop']"
                :label="column['label']">
            </el-table-column>
        </el-table>
        <div class="p-10">
            <el-button
                :disabled="tableData.length < 2"
                @click="exportExcel">
                {{ __('Экспорт в Excel') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import ReportTableMixin from '@/components/reports/interactive/mixins/report-table';

export default {
    mixins: [
        ReportTableMixin,
    ],
    data() {
        return {
            columns: [
                {prop: 'employee', label: __('ФИО сотрудника')},
                {prop: 'position', label: __('Отделение')},
                {prop: 'externalCard', label: __('Карта пациента')},
                {prop: 'externalPayed', label: __('Сумма дохода за внешнее перенаправление')},
                {prop: 'externalPercent', label: __('Оплата сотруднику')},
                {prop: 'internalCard', label: __('Карта пациента')},
                {prop: 'internalPayed', label: __('Сумма дохода за перенаправление')},
                {prop: 'internalPercent', label: __('Оплата сотруднику')},
                {prop: 'totalPayed', label: __('ДОХОД ИТОГО')},
                {prop: 'totalPercent', label: __('ОПЛАТА СОТРУДНИКУ ИТОГО')},
            ],
        }
    },
    methods: {
        tableRowClassName({row, rowIndex}) {
            if (row.isEmployeeSummary == true) {
                return 'row-summary';
            }
            if (row.isTableSummary) {
                return 'row-totals';
            }
            return '';
        },
        exportExcel() {
            this.$emit('export');
        },
    },
}
</script>