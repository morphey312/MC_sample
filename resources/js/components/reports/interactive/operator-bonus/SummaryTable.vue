<template>
    <div>
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            :max-height="getHeight()"
            header-row-class-name="light-grey">
            <el-table-column
                fixed
                width="250"
                :label="__('ФИО')"
                prop="full_name"
                class-name="label-light-blue">
            </el-table-column>
            <el-table-column
                v-for="(clinic) in clinics"
                :key="clinic.id"
                :label="clinic.name">
                <el-table-column
                    :label="__('kpi')"
                    :prop="`kpi_${clinic.id}`"
                    :formatter="percentFormatter"
                    width="80">
                </el-table-column>
                <el-table-column
                    :label="__('ставка')"
                    :prop="`rate_${clinic.id}`"
                    width="80">
                </el-table-column>
                <el-table-column
                    :label="__('приход')"
                    :prop="`incomes_${clinic.id}`"
                    width="80">
                </el-table-column>
                <el-table-column
                    :label="__('бонус')"
                    :prop="`bonus_${clinic.id}`"
                    width="80"
                    class-name="label-selected">
                </el-table-column>
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
        clinics: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        percentFormatter(row, column, cellValue, index) {
            return cellValue > 0 ? _.ceil(cellValue, 1) + '%' : '';
        },
        exportExcel() {
            this.$emit('export');
        }
    }
}
</script>