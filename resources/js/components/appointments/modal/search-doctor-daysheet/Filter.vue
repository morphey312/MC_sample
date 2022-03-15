<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :auto-search="false"
        :buttonSubmitText="__('Показать')"
        @changed="changed">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics_list"
                    :clearable="true"
                    property="clinic"
                    :filterable="true"
                    :label="__('Клиника')"
                    css-class="inline-block form-input"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :multiple="true"
                    :collapse-tags="true"
                    property="specialization"
                    css-class="inline-block form-input"
                    :label="__('Специализация')"
                />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="dateStart" />
                        <form-date
                            :entity="filter"
                            property="dateEnd"
                            :options="options" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-row
                    name="adjacentSpecialization"
                    label="&nbsp;">
                    <el-checkbox v-model="adjacentSpecialization">
                        {{ __('Открыть листы смежных специализаций') }}
                    </el-checkbox>
                </form-row>
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics_list: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments-sheets'),
            }),
            specializations: [],
            adjacentSpecialization:false,
            options: {
                firstDayOfWeek: 1,
                shortcuts: [{
                    text: __('Сегодня'),
                    onClick(picker) {
                        picker.$emit('pick', new Date());
                    },
                }],
            },
        }
    },
    watch: {
        ['filter.clinic']() {
            this.getSpecializations();
        },
        ['filter.dateStart'](val) {
            this.filter.dateEnd = val;
        },
        ['filter.dateEnd'](val) {
            if(val < this.filter.dateStart){
                this.filter.dateEnd = this.filter.dateStart;
            }
        },
        ['adjacentSpecialization'](val) {
            this.filter.adjacent = val;
        },
    },
    methods: {
        initFilter(fromState = {}) {
            let today = this.$moment().format('YYYY-MM-DD');

            this.filter = {
                clinic: null,
                dateStart: today,
                dateEnd: today,
                specialization: [],
                adjacent: false,
                weekDay: null,
                ownerName: null,
                timeFrom: null,
                timeTo: null,
                workspace_name: null,
                ...fromState,
            };
        },
        getSpecializations() {
            this.specializations = [];
            this.filter.specialization = [];

            if(this.filter.clinic){
                let specialization = new SpecializationRepository({
                    limitClinics: this.$isAccessLimited('appointments-sheets'),
                });
                specialization.fetchList({active_clinic: this.filter.clinic}).then((response) => {
                    this.specializations = response;
                });
            }
        },
        changed(filters) {
            if(!filters.clinic && filters.specialization.length === 0) {
                return false;
            }

            this.$emit('changed', this.makeFilters(filters));
        },
    }
}
</script>
