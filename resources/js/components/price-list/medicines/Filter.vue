<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="medicine_stores"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="store"
                    :label="__('Склад')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="name_i18n"
                    :label="__('Название')"
                    :clearable="true"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="code"
                    :label="__('Код')"
                    :clearable="true"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import MedicineStoreRepository from '@/repositories/medicine-store';
import ClinicRepository from '@/repositories/clinic';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            medicine_stores: [],
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
        };
    },
    mounted() {
        this.getMedicineStores();
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                name_i18n: null,
                code: null,
                store: null,
                clinic: null,
                ...fromState,
            };
        },
        getMedicineStores() {
            let store = new MedicineStoreRepository();
            store.fetchList().then((response) => {
                this.medicine_stores = response.map((row) => {
                    return {
                        id: row.id,
                        value: row.value + '(' + row.firm_description + ')',
                    };
                });
            });
        },
    },
};
</script>