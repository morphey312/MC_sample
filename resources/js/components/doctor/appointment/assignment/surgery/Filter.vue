<template>
	<search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="12">
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
    methods: {
    	initFilter(fromState = {}) {
            this.filter = {
                name: null,
                disabled: false,
                payment_destination_mark: null,
                hasPrice: {},
                ...fromState,
            };
        },
    },
}	

</script>