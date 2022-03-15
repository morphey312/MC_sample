<template>
    <page
        :title="__('Журнал причин задержки изменения статуса')"
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
                <delay-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            class="grey-cap shrinkable">
            <delay-list
                ref="table"
                :filters="filters"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$can('appointment-delays.export')"
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportData()" />
                </div>
            </delay-list>
        </section>
    </page>
</template>
<script>
import DelayFilter from './delay-journal/Filter.vue';
import DelayList from './delay-journal/List.vue';
import AppointmentDelayRepository from '@/repositories/appointment/delay';
import ManageMixin from '@/mixins/manage';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import * as delayGenerator from './delay-journal/generator';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        DelayFilter,
        DelayList,
    },
    data() {
        return {
            displayFilter: true,
            displayTable: false,
            reportRepository: new AppointmentDelayRepository(),
            loading: false,
            fileGenerator: delayGenerator,
        };
    },
    methods: {
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
        exportData() {
            this.fileGenerator.clearTotal();
            return this.exportExcel(null, null, (workbook, fields) => {
                let worksheet = workbook.worksheets[0];
                let fieldLength = fields.length;
                let row = {};
                let beforeLastField = fields[fieldLength - 2].name;
                let lastField = fields[fieldLength - 1].name;
                row[beforeLastField] = __('Общее время задержки');
                row[lastField] = this.$formatter.durationShortFormat(this.fileGenerator.getTotal());
                worksheet.addRow(row);
            });
        }
    },
}
</script>