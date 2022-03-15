<template>
    <transition name="slide" mode="out-in">
    <div class="schedule-selected-filters" v-show="filterVisibility">
        <search-filter
            :model="filter"
            @changed="changed"
            @cleared="cleared"
            :auto-search="false"
            :show-submit-button="true"
            :show-clear-button="true"
            :buttonSubmitText="__('Фильтровать')" >
            <el-row :gutter="20">
                <el-col :span="-15">
                    <form-select
                        :entity="filter"
                        :options="filters.clinics"
                        property="clinic"
                        :multiple="true"
                        :clearable="true"
                        :filterable="true"
                        :label="__('Клиника')" />
                </el-col>
                <el-col :span="-15">
                    <form-row
                        name="dates"
                        :label="__('Период')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filter"
                                property="date_from" />
                            <form-date
                                :entity="filter"
                                property="date_to"
                            />
                        </div>
                    </form-row>
                </el-col>
                <el-col :span="-15">
                    <form-select
                        :entity="filter"
                        :options="filters.specializations"
                        property="specialization"
                        :clearable="true"
                        :label="__('Специализация')" />
                </el-col>
                <el-col :span="-15">
                    <form-select
                        :entity="filter"
                        :options="filters.doctors"
                        property="doctor"
                        :clearable="true"
                        :label="__('Врач')" />
                </el-col>
            </el-row>
        </search-filter>
    </div>
    </transition>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: {
            type: Object,
            default: () => ({})
        },
    },
    data() {
        return {
            filter: {
                clinic: [],
                specialization: null,
                doctor: null,
                date_from: '',
                date_to: '',
                status: null
            },
            filterVisibility: true,
        }
    },
    created() {
        this.listenFilterToogle = () => {
            this.filterVisibility = !this.filterVisibility;
        }
    },
    mounted() {
        this.$eventHub.$on('toggle-filter-collapse', this.listenFilterToogle);
    },
    beforeDestroy() {
        this.$eventHub.$off('toggle-filter-collapse', this.listenFilterToogle);
    },
    methods: {
        changed(filters) {
            this.$emit('apply-filters', filters);
        },
        cleared() {
            for (let field in this.filter){
                this.filter[field] = null;
            }

            this.$emit('apply-filters', this.filter);
        },
    }
}
</script>
