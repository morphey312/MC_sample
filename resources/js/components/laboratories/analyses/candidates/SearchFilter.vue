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
                    <form-input
                        :entity="filter"
                        property="laboratoryCode"
                        :clearable="true"
                        :label="__('Код лаборатории')"/>
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="filter"
                        property="clinicCode"
                        :clearable="true"
                        :label="__('Код клиники')"/>
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="filter"
                        property="name"
                        :clearable="true"
                        :label="__('Название')"/>
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
        analysis: Object,
    },
    beforeMount() {
        this.filter.name = this.analysis.name;
        this.filter.laboratoryCode = this.analysis.code;
    },
    methods: {
        initFilter(fromState = {}){
            this.filter = {
                name: null,
                description: null,
                laboratoryCode: null,
                clinicCode: null,
                disabled: false,
                ...fromState,
            };
        },
    },
}	

</script>