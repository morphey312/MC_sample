<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            :max-height="getHeight()"
            :row-class-name="tableRowClassName">
            <el-table-column
                fixed
                prop="rowTitle"
                label="P"
                width="150">
            </el-table-column>
            <el-table-column 
                v-for="(spec, specIndex) in Object.keys(columns).sort()"
                :key="`spec-${specIndex}`"
                :label="spec"
                :label-class-name="`label-${colors[spec] || colors['default']}`">
                <el-table-column
                    v-for="(doctor, docIndex) in Object.keys(columns[spec])"
                    :key="`doctor-${docIndex}`"
                    :label="getDoctorName(doctor, columns[spec][doctor])"
                    width="120"
                    :label-class-name="`label-${colors[spec] || colors['default']}`">
                    <template slot-scope="scope">
                        <span>
                            {{ scope.row[`${spec}${doctor}`] }}    
                        </span>
                    </template>
                </el-table-column>
            </el-table-column>
            <el-table-column
                fixed="right"
                label="T"
                width="150"
                label-class-name="label-selected"
                class-name="row-selected">
                <template slot-scope="scope">
                    <span>
                        {{ scope.row.summary }}    
                    </span>
                </template>
            </el-table-column>
        </el-table>
        <div class="p-10">
            <el-button
                :disabled="tableData.length === 0"
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
    props: {
        columns: {
            type: Object,
            default: () => ({}),
        },
        colors: {
            type: Object,
            default: () => ({}),
        },
        sundays: {
            type: Array,
            default: () => [],
        },
        employees: {
            type: Object,
            default: () => ({}),
        },
    },
    methods: {
        getDoctorName(key, defaultVal = '') {
            return this.employees[key] || defaultVal;
        },
        tableRowClassName({row, rowIndex}) {
            if (this.sundays.indexOf(row.rowTitle) !== -1) {
                return 'row-selected';
            }
            return '';
        },
        exportExcel() {
            this.$emit('export');
        },
    }
}
</script>