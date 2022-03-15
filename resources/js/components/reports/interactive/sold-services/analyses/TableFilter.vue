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
                    :multiple="true"
                    :filterable="true"
                    :clearable="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="laboratories"
                    :multiple="true"
                    :clearable="true"
                    property="laboratory"
                    :label="__('Лаборатория')" />
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
        laboratories: Array,
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: [],
                laboratory: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                ...fromState,
            };
        },
    },
}
</script>

<style scoped>

</style>
