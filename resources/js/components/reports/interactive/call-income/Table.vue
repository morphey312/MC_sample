<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            :max-height="getHeight()"
            :row-class-name="tableRowClassName"
            header-row-class-name="light-grey">
            <el-table-column
                fixed
                prop="name"
                :label="__('Оператор')"
                width="250">
            </el-table-column>
            <el-table-column
                :label="__('Месяц')"
                prop="date"
                width="100">
            </el-table-column>
            <el-table-column
                :label="__('Звонки')"
                prop="calls"
                width="100">
            </el-table-column>
            <el-table-column
                :label="__('Записи')"
                prop="appointments"
                width="100">
            </el-table-column>
            <el-table-column
                :label="__('Приходы')"
                prop="income"
                width="100">
            </el-table-column>
            <el-table-column 
                :label="__('% записей')" 
                width="100">
                <template slot-scope="scope">
                    <span>
                        {{ getPercent(scope.row.appointments, scope.row.calls) }}    
                    </span>
                </template>
            </el-table-column>
            <el-table-column 
                :label="__('% приходов')"
                width="100">
                <template slot-scope="scope">
                    <span>
                        {{ getPercent(scope.row.income, scope.row.calls) }}    
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                :label="__('Не профильные')"
                prop="nonProfile"
                width="150">
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
    methods: {
        tableRowClassName({row, rowIndex}) {
            if (row.summary == true) {
                return 'row-summary';
            }
            if (row.isTableSummary) {
                return 'row-totals';
            }
            return '';
        },
        getPercent(divident, divider) {
            return divider > 0 ? Math.round(100 * divident / divider) : 0;
        },
        exportExcel() {
            this.$emit('export');
        },
    },
}
</script>