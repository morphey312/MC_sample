<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :button-submit-text="__('Показать')"
        :auto-search="false"
        @changed="changed">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    property="clinic"
                    :filterable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="positions"
                    :multiple="true"
                    :clearable="true"
                    property="position"
                    :label="__('Должность')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Дата')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="date_start"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="date_end"
                            :clearable="true" />
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
        clinics: Array,
        positions: Array,
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                position: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                ...fromState,
            };
        },
    },
};
</script>
