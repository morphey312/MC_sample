<template>
    <page
        :title="__('Архив блокировок времени')"
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
                <lock-log-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            class="grey-cap shrinkable">
            <lock-log-list
                ref="table"
                @selection="selection"
                :filters="filters"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$can('lock-log.excel')"
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('Архив Блокировок'))" />

                    <form-button
                        v-if="activeItem && activeItem.status == 1 && $canAccess('appointments-sheets')"
                        @click="routeScheduleWithParam"
                        :text="__('Открыть Лист записи')"
                         />
                </div>
            </lock-log-list>
        </section>
    </page>
</template>
<script>
import lts from '@/services/lts';
import LockLogFilter from './lock-log/Filter.vue';
import LockLogList from './lock-log/List.vue';
import LockLogRepository from '@/repositories/locklog';
import ManageMixin from '@/mixins/manage';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import * as lockLogGenerator from './lock-log/generator';
export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        LockLogFilter,
        LockLogList,
    },
    data() {
        return {
            activeItem: null,
            displayFilter: true,
            displayTable: false,
            reportRepository: new LockLogRepository(),
            loading: false,
            fileGenerator: lockLogGenerator,
        };
    },
    methods: {
        selection(item){
            this.activeItem = item;
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },

        routeScheduleWithParam() {
            delete lts.appointmentStore;
            lts.appointmentStore = this.getDaySheetParam();
            let routeData = this.$router.resolve({name: 'appointment-schedule'});
            window.open(routeData.href, '_blank');
        },

        getDaySheetParam() {
            return {
                daySheet: {
                    workspace_id: this.activeItem.daysheet.workspace_id,
                    date: this.activeItem.daysheet.date,
                    day_sheet_owner_id: this.activeItem.daysheet.day_sheet_owner_id,
                    day_sheet_owner_type: this.activeItem.daysheet.day_sheet_owner_type,
                    clinic_id: this.activeItem.daysheet.clinic_id,
                },
            };
        },

    },
}
</script>
