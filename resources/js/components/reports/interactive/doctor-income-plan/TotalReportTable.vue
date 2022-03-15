<template>
    <div>
        <div class="p-10">
            <b>{{ $formatter.dateFormat(month.month_start, 'MMMM YYYY') }}</b>
        </div>
        <el-table
            :data="tableData"
            style="width: 100%"
            class="table-sm"
            :border="true">
            <el-table-column
                fixed
                prop="title"
                :label="__('Клиника')"
                width="150" />
            <el-table-column
                fixed
                prop="col-percent"
                :label="__('%')"
                width="80" />
            <el-table-column
                prop="col-total"
                label=""
                width="100"
                :formatter="formatVal" />
            <el-table-column
                v-for="(clinic, clinicIndex) in sortedClinics"
                :key="clinicIndex"
                :prop="`col-${clinic}`"
                :label="clinic"
                width="100"
                :formatter="formatVal" />
        </el-table>
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
        month: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            sortedClinics: [],
        }
    },
    mounted() {
        this.sortedClinics = [...this.clinicsToDisplay].sort();
    },
    methods: {
        formatVal(row, column, cellValue, index) {
            if (row.valueFormatter) {
                return row.valueFormatter(cellValue);
            }
            return cellValue;
        },
    }
}
</script>