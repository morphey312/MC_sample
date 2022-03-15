<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="7">
                <form-select
                    :entity="filter"
                    :options="card_types"
                    property="discount_card_type"
                    :label="__('Тип карты')" />
            </el-col>
            <el-col :span="7">
                <form-input
                    :entity="filter"
                    property="number"
                    :label="__('Номер')" />
            </el-col>
            <el-col :span="7">
                <form-input
                    :entity="filter"
                    property="owner_last_name"
                    :label="__('Фамилия владельца')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import DiscountCardTypeRepository from '@/repositories/discount-card-type';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            card_types: [],
        };
    },
    mounted() {
        this.getCardTypes();
        this.$watch('filter.discount_card_type', (val) => {
            if (val) {
                let cardType = this.card_types.find(type => type.id == val);
                this.$emit('propose-disable', cardType.propose_to_disable_on_copy);
            } else {
                this.$emit('propose-disable', false);
            }
        });
    },
    methods: {
        getCardTypes() {
            let repo = new DiscountCardTypeRepository();
            repo.fetchList(this.getCardTypeFilters()).then((response) => {
                this.card_types = response;
            });
        },
        initFilter() {
            this.filter =  {
                discount_card_type: null,
                number: null,
                owner_last_name: null,
            };
        },
        getCardTypeFilters() {
            return _.onlyFilled({
                cant_be_copied: 0,
            });
        },
    },
};
</script>