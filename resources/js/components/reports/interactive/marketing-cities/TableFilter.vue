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
                    :options="informationSources"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Источник информации')"
                    property="information_sources"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
;

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
            this.$emit('changed-information-sources', this.getSpecializationFilters());
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
