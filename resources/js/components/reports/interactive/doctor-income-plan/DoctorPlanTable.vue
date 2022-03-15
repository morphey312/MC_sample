<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            class="table-sm"
            :border="true"
            :max-height="getHeight()"
            :span-method="spanMethod">
            <el-table-column
                fixed
                prop="name"
                :label="__('ФИО врача')"
                width="150">
            </el-table-column>
            <el-table-column
                fixed
                prop="sub_title"
                :label="__('Отделение')"
                width="150">
            </el-table-column>
            <el-table-column
                v-for="(month, mIndex) in Object.keys(months)"
                :key="mIndex"
                :label="$formatter.dateFormat(months[month].momented_start, 'MM.YYYY')"
                width="150">
                <el-table-column
                    width="100"
                    :label="__('План')"
                    :prop="`plan_income-${month}`">
                </el-table-column>
                <el-table-column
                    width="100"
                    :label="__('Среднедневная запланированная выручка')"
                    :prop="`plan_avg_by_session-${month}`">
                </el-table-column>
                <el-table-column
                    v-for="(week, weekIndex) in (months[month].weeks)"
                    :key="weekIndex"
                    width="100"
                    :label="`${$formatter.dateFormat(week.start, 'DD')}-${$formatter.dateFormat(week.end, 'DD.MM.YYYY')}`"
                    :prop="`fact_week-${month}-${weekIndex}`" >
                </el-table-column>
                <el-table-column
                    width="100"
                    :label="__('Итого')"
                    class-name="label-light-blue"
                    cell-class-name="label-light-blue"
                    :prop="`fact_income-${month}`">
                </el-table-column>
            </el-table-column>
            <el-table-column
                fixed="right"
                prop="total_planned"
                :label="__('План')"
                label-class-name="label-selected"
                class-name="row-selected"
                width="90">
            </el-table-column>
            <el-table-column
                fixed="right"
                prop="total_fact"
                :label="__('Выполнение')"
                label-class-name="label-selected"
                class-name="row-selected"
                width="90">
            </el-table-column>
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
        months: {
            type: Object,
            default: () => ({}),
        },
    },
    methods: {
        spanMethod({ row, column, rowIndex, columnIndex }) {
            if (columnIndex === 0) {
                if (_.isFilled(row.name)) {
                    return {
                        rowspan: 7,
                        colspan: 1
                    }
                }
                return {
                    rowspan: 0,
                    colspan: 0
                };
            }
        },
    }
}
</script>