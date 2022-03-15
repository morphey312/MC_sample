<template>
    <page
        :title="__('Банк анализов')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey">
                <result-filter
                    ref="filter"
                    :initial-state="filters"
                    :active-tab="activeTab"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" v-if="displayTable" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('В записи')"
                name="results" >
                <section class="pt-0 shrinkable">
                    <results
                        ref="results"
                        :filters="filters"
                        @syncFilters="syncFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Назначенные')"
                name="assigned" >
                <section class="pt-0 shrinkable">
                    <assigned
                        ref="assigned"
                        :filters="filters"
                        @syncFilters="syncFilters" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import ResultFilter from './analysis-results/Filter.vue';
import Results from './analysis-results/Results.vue';
import Assigned from './analysis-results/Assigned.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ResultFilter,
        Results,
        Assigned,
    },
    data() {
        return {
            displayFilter: true,
            activeTab: 'results',
            displayTable: false,
        };
    },
    methods: {
        getDefaultFilters() {
            return {
                has_appointment: true,
                clinic: this.getLoggedUserClinics(),
                status: [CONSTANTS.ANALYSIS_RESULT.STATUSES.PASSED],
            };
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
    },
}
</script>
