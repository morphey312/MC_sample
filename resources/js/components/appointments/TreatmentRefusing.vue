<template>
    <page
        :title="__('Причины не взятия лечения')"
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
                <refuse-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            class="grey-cap shrinkable">
            <refuse-list
                ref="table"
                :filters="filters"
                @loaded="refreshed"
                @selection-changed="setActiveItem"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel()" />
                    <form-button 
                        v-if="$can('patient-cabinet.access')"
                        :disabled="activeItem === null"
                        :text="__('Записи пациента')"
                        icon="badge"
                        @click="patientAppointments" />
                </div>
            </refuse-list>
        </section>
    </page>
</template>
<script>
import RefuseFilter from './treatment-refusing/Filter.vue';
import RefuseList from './treatment-refusing/List.vue';
import AppointmentRepository from '@/repositories/appointment';
import ManageMixin from '@/mixins/manage';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import * as delayGenerator from './treatment-refusing/generator';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        RefuseFilter,
        RefuseList,
    },
    data() {
        return {
            displayFilter: true,
            displayTable: false,
            reportRepository: new AppointmentRepository(),
            loading: false,
            fileGenerator: delayGenerator,
        };
    },
    methods: {
        getDefaultFilters() {
            return {
                has_rejection_reason: 1,
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
        patientAppointments() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-calls', params: {patientId: this.activeItem.patient_id}});
            window.open(routeData.href, '_blank');
        },
    },
}
</script>