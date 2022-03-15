<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            class="table-sm"
            :border="true"
            :max-height="getHeight()">
            <el-table-column
                prop="clinic_name"
                :label="__('Клиника')"
                width="200">
            </el-table-column>
            <el-table-column
                prop="specialization"
                :label="__('Специализация')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="employee"
                :label="__('Врач')"
                width="200">
            </el-table-column>
            <el-table-column
                prop="plan"
                :label="__('План')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="fact"
                :label="__('Факт')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="progression"
                :label="__('Выполнение плана')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="is_first"
                :label="__('I пац')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="repeated"
                :label="__('II пац.')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="treatments"
                :label="__('Взявшие леч.')"
                width="100">
            </el-table-column>
            <el-table-column
                prop="treatment_percent"
                :label="__('% за лечение')"
                width="100"
                :formatter="addPercent">
            </el-table-column>
            <el-table-column
                prop="avg_check"
                :label="__('Средний чек')"
                width="100">
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
}
</script>
