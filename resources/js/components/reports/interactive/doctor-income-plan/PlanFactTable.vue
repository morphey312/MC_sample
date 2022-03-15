<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            class="table-sm"
            :border="true"
            :max-height="getHeight()"
            :cell-class-name="getCellClass">
            <el-table-column
                fixed
                prop="name"
                :label="__('Клиника')"
                width="150">
            </el-table-column>
            <el-table-column
                v-for="(month, mIndex) in Object.keys(months)"
                :key="mIndex"
                label="">
                <el-table-column
                    label="">
                    <el-table-column
                        width="100"
                        :label="`${__('План')} ${monthLabel(month)}`"
                        :prop="`plan-${month}`">
                    </el-table-column>
                    <el-table-column
                        width="100"
                        :label="`${__('Факт')} ${monthLabel(month)}`"
                        :prop="`fact-${month}`">
                    </el-table-column>
                </el-table-column>
                <el-table-column>
                    <el-table-column
                        width="70"
                        :label="__('Валюта')"
                        prop="currency"
                    ></el-table-column>
                </el-table-column>
                <el-table-column>
                    <span slot="header" slot-scope="scope">
                        {{ __('Прогноз') }}<br>
                        <span class="table-text-red">{{ __('отставание') }}</span>/<span class="table-text-green">{{ __('перевыполнение') }}</span>
                    </span>
                    <el-table-column
                        width="100"
                        :label="__('в абсолют.выраж.')"
                        :prop="`progression-${month}`">
                    </el-table-column>
                    <el-table-column
                        width="100"
                        :label="__('в процентах')"
                        :prop="`progression-percent-${month}`"
                        :formatter="addPercent">
                    </el-table-column>
                </el-table-column>
                <el-table-column width=30 />
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
            type: [Object, Array],
            default: () => ({}),
        },
    },
    methods: {
        getCellClass({row, column, rowIndex, columnIndex}) {
            if (rowIndex == 0) {
                return '';
            }
            if (column.property && column.property.indexOf('progression') != -1) {
                if (row[column.property] < 0) {
                    return 'table-text-red';
                }
                return 'table-text-green';
            }
            return '';
        },
        monthLabel(month) {
            return this.$formatter.dateFormat(this.months[month].momented_end, 'M/YYYY')
        },
    }
}
</script>
