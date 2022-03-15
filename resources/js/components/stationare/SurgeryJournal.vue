<template>
    <page
        :title="__('Журнал оперативных вмешательств')"
        v-loading="loading"
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
                <surgery-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <surgery-list 
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('patient-cabinet.access')"
                        :disabled="activeItem === null"
                        :text="__('Записи пациента')"
                        icon="badge"
                        @click="patientAppointments" />
                    <form-button 
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('Журнал запису оперативних втручань у стаціонарі'))" />
                </div>
            </surgery-list>
        </section>
    </page>
</template>
<script>
import SurgeryList from './surgery-journal/List.vue';
import SurgeryFilter from './surgery-journal/Filter.vue';
import ManageMixin from '@/mixins/manage';
import ProxyRepository from '@/repositories/proxy-repository';
import AppointmentRepository from '@/repositories/appointment';
import * as journalGenerator from './surgery-journal/generator';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        SurgeryList,
        SurgeryFilter,
    },
    data() {
        return {
            displayFilter: true,
            loading: false,
            reportRepository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repo = new AppointmentRepository();
                return repo.fetchSurgeryList(filters, sort, scopes, page, limit);
            }),
            fileGenerator: journalGenerator,
        };
    },
    methods: {
        getDefaultFilters() {
            return {
                has_surgery_service: 1,
                is_deleted: 0,
            };
        },
        patientAppointments() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-courses', params: {patientId: this.activeItem.patient_id}});
            window.open(routeData.href, '_blank');
        },
    },
}
</script>