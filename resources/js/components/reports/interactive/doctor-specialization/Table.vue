<template>
    <div class="marketing-report-v2">
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            header-row-class-name="label-light-blue text-white"
            :row-class-name="tableRowClassName"
            highlight-current-row
        >
            <el-table-column
                :label="__('Врач')"
                width="250"
                label-class-name="label-deep-blue"
                fixed
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.name }}
                </template>
            </el-table-column>
            <el-table-column
                :label="__('Первичные')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.first_count }}
                </template>
            </el-table-column>
            <el-table-column
                :label="__('Вторичные')"
                width="100"
                label-class-name="label-grey"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.second_count }}
                </template>
            </el-table-column>
            <el-table-column
                :label="__('Процент вторичных пациентов')"
                width="100"
                label-class-name="label-grey"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.secondPercentage + '%' }}
                </template>
            </el-table-column>
            <el-table-column
                :label="__('Оборот')"
                width="100"
                label-class-name="label-grey"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.total }}
                </template>
            </el-table-column>
            <el-table-column
                :label="__('Средний чек на консультации + обследование')"
                width="100"
                label-class-name="label-grey label-border-right"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.averageConsultationsExploration }}
                </template>
            </el-table-column>
            <el-table-column
                prop="total_calls"
                :label="__('Консультации')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.totalConsultationPayments }}
                </template>
            </el-table-column>
            <el-table-column
                prop="total_appointments"
                :label="__('Процент от общего оборота')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.averageConsultations + '%' }}
                </template>
            </el-table-column>
            <el-table-column
                prop="total_incomes"
                :label="__('Диагностика / рентген')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.totalXrayDiagnosticsPayments }}
                </template>
            </el-table-column>
            <el-table-column
                prop="total_payments"
                :label="__('Процент от общего оборота')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.averageDiagnostics + '%' }}
                </template>
            </el-table-column>
            <el-table-column
                prop="total_treatments"
                :label="__('Анализы')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.totalAnalisysPayments }}
                </template>
            </el-table-column>
            <el-table-column
                prop="total_payments"
                :label="__('Процент от общего оборота')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.averageAnalizys + '%' }}
                </template>
            </el-table-column>
            <el-table-column
                prop="predict_calls"
                :label="__('УЗИ')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.totalSonarsPayments }}
                </template>
            </el-table-column>
            <el-table-column
                prop="predict_treatments"
                :label="__('Процент от общего оборота')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.averageSonars + '%' }}
                </template>
            </el-table-column>
            <el-table-column
                prop="predict_percent"
                :label="__('Лечение')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{  scope.row.totalTreatmentsPayments }}
                </template>
            </el-table-column>
            <el-table-column
                prop="predict_percent"
                :label="__('Процент от общего оборота')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.averageTreatments + '%' }}
                </template>
            </el-table-column>
            <el-table-column
                prop="predict_percent"
                :label="__('Итого оборот')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            >
                <template
                    slot-scope="scope"
                >
                    {{ scope.row.total }}
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
        informationSources: Array,
        specializations: Array,
        mediaTypes: Array,
    },
    methods: {
        getSpecializationShortNameById(specializationId) {
            const specialization = _.find(this.specializations, ['id', specializationId])

            return specialization ? specialization.short_name : null;
        },
        exportExcel() {
            this.$emit('export');
        },
        tableRowClassName({row, index}) {
            if (row.is_total_by_media_type) {
                return 'total-media-type-row';
            }

            if (row.is_total || row.is_total_for_month_ago || row.is_total_for_year_ago) {
                return 'is-total-row';
            }
        }
    }
}
</script>
