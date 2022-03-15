<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :button-submit-text="__('Показать')"
        :auto-search="false"
        @changed="changed"
    >
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Специализация')"
                    property="specialization"
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
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        mediaTypes: Array,
        specializations: Array,
    },
    watch: {
        ['filter.clinic']() {
            this.$emit('changed-specializations', this.getSpecializationFilters());
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                specialization: [],
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
            });
        },
    },
};
</script>
