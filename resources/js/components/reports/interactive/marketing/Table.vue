<template>
    <div class="marketing-report-v2">
        <el-table
            :data="tableData"
            style="width: 100%"
            :border="true"
            :max-height="getHeight()"
            header-row-class-name="light-grey text-white"
            :row-class-name="tableRowClassName"
            highlight-current-row
        >
            <el-table-column
                :label="__('Вид рекламы')"
                width="250"
                label-class-name="label-deep-blue"
                fixed
            >
                <template
                    slot-scope="scope"
                >
                    {{ getRowName(scope.row, scope.$index) }}
                </template>
            </el-table-column>
            <el-table-column
                v-for="selectedSpecialization in selectedSpecializations"
                :key="selectedSpecialization"
                :label="getSpecializationShortNameById(selectedSpecialization)"
                label-class-name="label-deep-green"
            >
                <el-table-column
                    :label="__('Звонки')"
                    width="100"
                    label-class-name="label-grey"
                    class-name="label-border-left"
                >
                    <template
                        slot-scope="scope"
                    >
                        {{ scope.row.is_total_percentages ? '-' : (scope.row[`calls_${selectedSpecialization}`] || 0) }}
                    </template>
                </el-table-column>
                <el-table-column
                    :label="__('Записи')"
                    width="100"
                    label-class-name="label-grey"
                >
                    <template
                        slot-scope="scope"
                    >
                        {{ scope.row[`appointments_${selectedSpecialization}`] || 0 }}
                    </template>
                </el-table-column>
                <el-table-column
                    :label="__('Приходы')"
                    width="100"
                    label-class-name="label-grey"
                >
                    <template
                        slot-scope="scope"
                    >
                        {{ scope.row[`incomes_${selectedSpecialization}`] || 0 }}
                    </template>
                </el-table-column>
                <el-table-column
                    :label="__('Лечения')"
                    width="100"
                    label-class-name="label-grey"
                >
                    <template
                        slot-scope="scope"
                    >
                        {{ scope.row[`treatments_${selectedSpecialization}`] || 0 }}
                    </template>
                </el-table-column>
                <el-table-column
                    :label="__('Оборот')"
                    width="100"
                    label-class-name="label-grey label-border-right"
                >
                    <template
                        slot-scope="scope"
                    >
                        {{ scope.row.is_total_percentages ? '-' : (scope.row[`payments_${selectedSpecialization}`] || 0) }}
                    </template>
                </el-table-column>
            </el-table-column>
            <el-table-column
                :label="__('Итого')"
                label-class-name="label-deep-green"
            >
                <el-table-column
                    prop="total_calls"
                    :label="__('Звонки')"
                    width="100"
                    label-class-name="label-grey"
                    class-name="label-border-left"
                ></el-table-column>
                <el-table-column
                    prop="total_appointments"
                    :label="__('Записи')"
                    width="100"
                    label-class-name="label-grey"
                    class-name="label-border-left"
                ></el-table-column>
                <el-table-column
                    prop="total_incomes"
                    :label="__('Приходы')"
                    width="100"
                    label-class-name="label-grey"
                    class-name="label-border-left"
                ></el-table-column>
                <el-table-column
                    prop="total_treatments"
                    :label="__('Лечения')"
                    width="100"
                    label-class-name="label-grey"
                    class-name="label-border-left"
                ></el-table-column>
                <el-table-column
                    prop="total_payments"
                    :label="__('Оборот')"
                    width="100"
                    label-class-name="label-grey"
                    class-name="label-border-left"
                ></el-table-column>
            </el-table-column>
            <el-table-column
                prop="predict_calls"
                :label="__('Прогноз звонки')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            ></el-table-column>
            <el-table-column
                prop="predict_treatments"
                :label="__('Прогноз лечения')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            ></el-table-column>
            <el-table-column
                prop="predict_percent"
                :label="__('% по видам рекламы')"
                width="100"
                label-class-name="label-grey"
                class-name="label-border-left"
            ></el-table-column>
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
        selectedSpecializations: Array,
        specializations: Array,
        mediaTypes: Array,
    },
    methods: {
        exportExcel() {
            this.$emit('export');
        },

        getRowName(row) {
            const source = _.find(this.informationSources, ['id', parseInt(row.source, 10)])

            if (row.is_total_by_media_type) {
                const mediaType = _.find(this.mediaTypes, ['id', row.media_type])
                return mediaType ? mediaType.value : null;
            }

            if (row.is_total) {
                return __('Итого');
            }

            if (row.is_predicts) {
                return __('Прогноз');
            }

            if (row.is_percent_by_predict) {
                return ''
            }

            if (row.is_total_for_month_ago || row.is_total_for_year_ago) {
                return `${this.$moment(row.date_start).format("DD MMM YYYY")} - ${this.$moment(row.date_end).format("DD MMM YYYY")}`
            }

            return source ? source.value : null;
        },

        getSpecializationShortNameById(specializationId) {
            const specialization = _.find(this.specializations, ['id', specializationId])

            return specialization ? specialization.short_name : null;
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
