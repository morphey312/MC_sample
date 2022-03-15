<template>
    <page
        :title="__('Отчет по медикаментам')"
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
                <issued-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <el-tabs
            v-if="displayTable"
            v-model="activeTab"
            class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Все выданные препараты')"
                name="common">
                <section class="darkgrey-cap shrinkable pt-0">
                    <issued-all-list
                        ref="commonTable"
                        :filters="filters"
                        @syncFilters="syncFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Выданные платные препараты')"
                name="paylist">
                <section class="darkgrey-cap shrinkable pt-0">
                    <issued-pay-list
                        ref="payTable"
                        :filters="filters"
                        @syncFilters="syncFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Выданные препараты в рамках курса')"
                name="freelist">
                <section class="darkgrey-cap shrinkable pt-0">
                    <issued-free-list
                        ref="freeTable"
                        :filters="filters"
                        @syncFilters="syncFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Все назначенные препараты')"
                name="assigned">
                <section class="darkgrey-cap shrinkable pt-0">
                    <assigned-list
                        ref="assignedTable"
                        :filters="filters"
                        @syncFilters="syncFilters" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import IssuedFilter from './issued-medicines/Filter.vue';
import IssuedPayList from './issued-medicines/IssuedPayList.vue';
import IssuedFreeList from './issued-medicines/IssuedFreeList.vue';
import IssuedAllList from './issued-medicines/IssuedAllList.vue';
import AssignedList from './issued-medicines/AssignedList.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components:{
        IssuedFilter,
        IssuedPayList,
        IssuedFreeList,
        IssuedAllList,
        AssignedList,
    },
    data() {
        return {
            activeTab: 'common',
            displayFilter: true,
            displayTable: false
        }
    },
    methods: {
        refresh() {
            if (this.displayTable) {
                this.getManageTable().refresh();
            }
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
        getDefaultFilters() {
            return {
                clinic: this.getLoggedUserClinics(),
            };
        },
    }
}
</script>
