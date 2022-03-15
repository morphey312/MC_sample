<template>
    <el-row :gutter="20">
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
                            :label="__('Код лаборатории')"/>
                    </el-col>
                    <el-col :span="8">
                        <form-input
                            :entity="filter"
                            property="clinicCode"
                            :label="__('Код клиники')"/>
                    </el-col>
                    <el-col :span="8">
                        <form-input
                            :entity="filter"
                            property="name"
                            :label="__('Название')"/>
                    </el-col>
                </el-row>
        </search-filter>
    </el-row>
</template>
<script>
import FilterMixin from '@/mixins/filter';

export default {
	mixins: [
        FilterMixin,
    ],
    beforeMount() {
        this.initFilter();
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