<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :button-submit-text="__('Показать')"
        :auto-search="false"
        @changed="changed"
    >
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Дата')"
                >
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="date_start"
                            :clearable="true"
                        />
                        <form-date
                            :entity="filter"
                            property="date_end"
                            :clearable="true"
                        />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Специализация карты')"
                    property="specialization"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="mediaTypes"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Вид рекламы')"
                    property="media_types"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="informationSources"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Источник информации')"
                    property="information_sources"
                    style="margin-top: 10px"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from "@/repositories/specialization";
import InformationSources from "@/repositories/patient/information-source";

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        mediaTypes: Array,
        specializations: Array,
        clinics: Array,
        informationSources: Array,
    },
    watch: {
        ['filter.clinic']() {
            this.$emit('changed-specializations', this.getSpecializationFilters());
        },
        ['filter.media_types']() {
            this.$emit('changed-information-sources', this.getInformationSourcesFilters());
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                clinic: null,
                specialization: [],
                media_types: [],
                information_sources: [],
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
            });
        },
        getInformationSourcesFilters() {
            return _.onlyFilled({
                media_type: this.filter.media_types,
            });
        },
    },
};
</script>
