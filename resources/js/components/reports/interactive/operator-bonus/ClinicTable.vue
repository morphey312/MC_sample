<template>
    <el-table
        :data="tableData"
        style="width: 100%"
        :border="true"
        :max-height="getHeight()"
        header-row-class-name="light-grey">
        <el-table-column
            fixed
            width="250">
            <el-table-column
                :label="__('Ключевые показатели')"
                prop="indicators"
                width="200"
                class-name="label-light-blue">
            </el-table-column>
            <el-table-column
                :label="__('Вес')"
                prop="weight"
                width="50"
                class-name="label-light-blue">
            </el-table-column>
        </el-table-column>
        <el-table-column
            v-for="(operatorId) in Object.keys(operatorNames)"
            :key="operatorId"
            :label="operatorNames[operatorId]">
            <el-table-column
                :label="__('Норма')"
                :prop="`norm_${operatorId}`"
                :formatter="formatPart"
                width="70"
                class-name="label-selected">
            </el-table-column>
            <el-table-column
                :label="__('Факт')"
                :prop="`fact_${operatorId}`"
                :formatter="formatPart"
                width="70">
            </el-table-column>
            <el-table-column
                :label="__('Индекс kpi')"
                :prop="`index_${operatorId}`"
                :formatter="formatAll"
                width="90">
            </el-table-column>
        </el-table-column>
    </el-table>
</template>
<script>
import ReportTableMixin from '@/components/reports/interactive/mixins/report-table';

export default {
    mixins: [
        ReportTableMixin,
    ],
    props: {
        operatorNames: {
            type: Object,
            default: () => ({}),
        },
    },
    methods: {
        formatPart(row, column, cellValue, index) {
            if (_.isVoid(cellValue)) {
                return '';
            }
            return cellValue + (index < 2 ? '%' : '');
        },
        formatAll(row, column, cellValue, index) {
            if (_.isVoid(cellValue)) {
                return '';
            }
            return cellValue + '%';
        },
    },
}
</script>