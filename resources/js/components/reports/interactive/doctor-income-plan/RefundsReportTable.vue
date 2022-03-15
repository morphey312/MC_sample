<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            class="table-sm"
            :border="true"
            :max-height="getHeight()">
            <el-table-column
                fixed
                prop="clinic"
                :label="__('Клиника')"
                width="170">
            </el-table-column>
            <el-table-column
                fixed
                prop="specialization"
                :label="__('Специализация')"
                width="170">
            </el-table-column>
            <el-table-column
                fixed
                prop="doctor"
                :label="__('ФИО')"
                width="250">
            </el-table-column>
            <el-table-column
                fixed
                prop="amount"
                :label="__('Сумма возвратов')"
                width="100"
                :formatter="numberFormat">
            </el-table-column>
            <el-table-column
                fixed
                prop="date"
                :label="__('Дата возврата')"
                width="120"
                :formatter="dateFormat">
            </el-table-column>
        </el-table>
        <slot name="buttons" />
    </div>
</template>
<script>
import ReportTableMixin from '@/components/reports/interactive/mixins/report-table';

export default {
    mixins: [
        ReportTableMixin,
    ],
    methods: {
        numberFormat(row, column, cellValue) {
            return this.$formatter.numberFormat(cellValue);
        },
        dateFormat(row, column, cellValue) {
            return this.$formatter.dateFormat(cellValue);
        },
    }
}
</script>