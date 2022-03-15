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
                :label="__('Город')"
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
        selectedSpecializations: Array,
        specializations: Array,
        cities:Array,
        mediaTypes: Array,
    },
    methods: {
        getRowName(row) {
            const source = row.patient_location

            if (row.is_total) {
                return __('Итого');
            }

            return source ? source : null;
        },
        exportExcel() {
            this.$emit('export');
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
