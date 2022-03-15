<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        :button-submit-text="__('Фильтровать')"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    property="clinic"
                    :multiple="true"
                    :filterable="true"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')"
                />
            </el-col>
            <el-col :span="6">
                <form-date
                    :entity="filter"
                    property="dateFrom"
                    :clearable="true"
                    :label="__('Дата начала ограничения')"
                />
            </el-col>
            <el-col :span="6">
                <form-date
                    :entity="filter"
                    property="dateTo"
                    :clearable="true"
                    :label="__('Дата окончания ограничения')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';

export default {
    mixins: [
        FilterMixin
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('limitations'),
            }),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('limitations'),
                filters: this.getSpecializationsFilters(),
            }),
        }
    },
    mounted() {
        this.$watch('filter.dateFrom', (val) => {
            if(val) {
                this.filter.dateTo = this.$moment(val).endOf('month').format('YYYY-MM-DD');
            }
        })
    },
    methods: {
        initFilter(fromState = {}) {
            let from = this.$moment().add(1, 'month').startOf('month').format('YYYY-MM-DD');
            let to = this.$moment().add(1, 'month').endOf('month').format('YYYY-MM-DD');

            this.filter = {
                clinic: null,
                specialization: null,
                dateFrom: from,
                dateTo: to,
                limitation: null,
                ...fromState,
            };
        },
        getSpecializationsFilters() {
            return _.onlyFilled({
                clinic: this.filter ? this.filter.clinic : null,
            });
        },
    },
    watch: {
        ['filter.clinic']() {
            this.specializations.setFilters(this.getSpecializationsFilters());
        },
    },
}
</script>
