<template>
    <search-filter
        ref="filter"
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="name"
                    :label="__('Название')"
                    error-prefix="filter"/>
            </el-col>
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="laboratory_code"
                    :label="__('Код анализа лаборатории')"/>
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import BaseFilterMixin from '@/mixins/treatment/filter/base-filter';

export default {
    mixins: [
        FilterMixin,
        BaseFilterMixin,
    ],
    props: {
        laboratories: {
            type: Array,
            default: () => []
        },
    },
    
};
</script>
