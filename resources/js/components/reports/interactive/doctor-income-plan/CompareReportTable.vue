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
                prop="name"
                label=""
                width="150">
            </el-table-column>
            <el-table-column
                prop="period-first"
                label=""
                width="80">
            </el-table-column>
            <el-table-column
                v-for="(clinic, index) in clinicsToDisplay"
                :key="index"
                :prop="`clinic-${clinic.id}`"
                :label="clinic.value"
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
    props: {
        clinicsToDisplay: {
            type: Array,
            default: () => [],
        },
    },
}
</script>