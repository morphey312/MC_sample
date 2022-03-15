<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-row 
                    name="period"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="dateStart"
                            :editable="false"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="dateEnd"
                            :editable="false"
                            :clearable="true" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="currencies"
                    property="code"
                    :multiple="true"
                    :clearable="true"
                    :label="__('Валюта')" />
            </el-col>
        </el-row>
    </search-filter>
</template>
<script>
import FilterMixin from '@/mixins/filter';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            currencies: this.$handbook.getOptions('currency').filter(cur => cur.id !== CONSTANTS.CURRENCY.UAH),
        }
    },
    methods: {
        initFilter(fromState = {}){
            let today = this.$moment().format('YYYY-MM-DD');

            this.filter = {
                dateStart: today,
                dateEnd: today,
                code: [],
                ...fromState,
            };
        },
    }
}
</script>