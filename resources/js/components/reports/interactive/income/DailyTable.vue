<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            :max-height="getHeight()"
            show-summary
            header-row-class-name="light-grey">
            <el-table-column
                fixed
                width="100"
                :label="__('Период, день')"
                prop="date"
                class-name="label-light-blue">
            </el-table-column>
            <el-table-column
                v-for="(clinic) in clinics"
                :key="clinic.id"
                :label="clinicLabelMap[clinic.value]"
                width="100"
                :prop="`value-${clinic.id}`">
            </el-table-column>
            <el-table-column
                width="100"
                :label="__('Итого')"
                prop="total"
                class-name="label-light-blue">
            </el-table-column>
        </el-table>
    </div>
</template>
<script>
import ReportTableMixin from '@/components/reports/interactive/mixins/report-table';
import ChartFormat from '@/components/reports/interactive/mixins/chart-format';

export default {
    mixins: [
        ReportTableMixin,
        ChartFormat,
    ],
    props: {
        clinics: {
            type: Array,
            default: () => [],
        },
    },
}
</script>