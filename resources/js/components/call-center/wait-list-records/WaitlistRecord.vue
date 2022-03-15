<template>
    <page
        :title="__('База ожидания')"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')"
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
            <section class="grey filter">
                <record-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            class="grey-cap shrinkable">
            <record-list
                ref="table"
                :filters="filters"
                :flex-height="true"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('База ожидания'))" />
                </div>
            </record-list>
        </section>
    </page>
</template>
<script>
import RecordFilter from './Filter.vue';
import RecordList from './List.vue';
import ManageMixin from '@/mixins/manage';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import WaitListRecordsRepository from '@/repositories/wait-list-record';
import * as waitListRecordsGenerator from './generators/records';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        RecordFilter,
        RecordList,
    },
    data() {
        return {
            reportRepository: new WaitListRecordsRepository(),
            fileGenerator: waitListRecordsGenerator,
            displayFilter: true,
            displayTable: false,
            loading: false,
        }
    },
    methods: {
        getDefaultFilters() {
            return {
                period_from: this.$moment().format('YYYY-MM-DD'),
                period_to: this.$moment().format('YYYY-MM-DD'),
                ...(this.$isAccessLimited('wait-list-record') 
                    ? {clinic: this.getLoggedUserClinics()} 
                    : {}
                ),
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
