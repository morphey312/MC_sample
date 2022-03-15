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
                :label="__('Дата')"
                prop="started_at"
                width="200">
            </el-table-column>
            <el-table-column
                :label="__('Кол-во входящих звонков')"
                prop="countCalls"
                width="110">
            </el-table-column>
            <el-table-column
                :label="__('Кол-во пропущенных звонков')"
                prop="countMissed"
                width="150">
            </el-table-column>
            <el-table-column
                :label="__('% пропущенных звонков')"
                prop="percentMissed"
                width="110">
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
