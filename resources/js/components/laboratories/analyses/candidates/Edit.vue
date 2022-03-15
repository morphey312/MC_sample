<template>
    <div class="page">
        <section class="grey pt-10">
            <search-filter 
                ref="filter"
                :analysis="model"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <empty-section 
            v-if="emptyFilters" 
            wrapper-class="wrapper-large">
            {{ __('Выберите код лаборатории, клиники или название анализа') }}
        </empty-section>
        <section 
            v-else
            class="grey-cap pt-0 pb-0 flex-content"
            style="height: 325px;">
            <search-table
                ref="table"
                :filters="filters"
                @loaded="refreshed"
                :laboratories="laboratories"
                @header-filter-updated="syncFilters"
                @selection-changed="addToSelected" />
            <div class="mt-20" v-if="selectedRow">
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :disabled="true"
                            :label="__('Название анализа из MC+LAB')"/>  
                    </el-col>
                    <el-col :span="12">
                        <form-input
                            :entity="selectedRow"
                            property="name"
                            :disabled="true"
                            :label="__('Название анализа в MC+')"/>  
                    </el-col>
                </el-row>
            </div>
        </section>
        <div class="form-footer text-right mr-0 ml-0">
             <el-button 
                @click="cancel">
                {{ __('Отменить') }}
                </el-button>
            <el-button
                type="primary"
                @click="connect">
                {{ __('Связать') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import AnalysisRepository from '@/repositories/analysis';
import SearchFilter from './SearchFilter.vue';
import EmptySection from './Empty.vue';
import SearchTable from './SearchTable.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        SearchFilter,
        SearchTable,
        EmptySection,
    },
    props: {
        model: Object,
        laboratories: {
            type: Array,
            default: () => []
        },
    },
    data(){
        return {
            repository: new AnalysisRepository,
            selectedRow: null,
        }
    },
    computed: {
        emptyFilters() {
            return _.isVoid(this.filters.clinicCode) &&
                   _.isVoid(this.filters.laboratoryCode) &&
                   _.isVoid(this.filters.name);
        },
        hasFilters() {
            return !this.emptyFilters;
        },
    },
    methods: {
        getDefaultFilters() {
        return {
            laboratory: this.laboratories.map(lab => lab.id)
            };
        },
        changeFilters(filters) {
            this.filters = filters; 
        },
        addToSelected({row, index}) {
            this.selectedRow = row;
        },
        cancel() {
            this.$emit('cancel');
        },
        connect(){
            this.selectedRow.candidate_id = this.model.id;
            this.$emit('connect', this.selectedRow);
        }
    },
}
</script>