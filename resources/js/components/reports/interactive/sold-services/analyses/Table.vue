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
                :label="__('Название анализа')"
                prop="analysis"
                width="250">
            </el-table-column>
            <el-table-column
                prop="clinic"
                :label="__('Клиника')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="analysis_code"
                :label="__('Код анализа лаборатории')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="analysis_code_clinic"
                :label="__('Код анализа клиники')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="laboratory"
                :label="__('Лаборатория')"
                width="100">
            </el-table-column>
            <el-table-column
                :label="__('Количество')"
                prop="count"
                width="100">
            </el-table-column>
            <el-table-column
                :label="__('Продано на сумму')"
                prop="payed"
                width="100">
            </el-table-column>
            <el-table-column
                :label="__('Оплачено')"
                prop="payments"
                width="100">
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
        exportExcel() {
            this.$emit('export');
        },
    },
}
</script>

<style scoped>

</style>
