<template>
    <page
        :title="__('Журнал учета больных в стационаре')"
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
                <journal-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <journal-list 
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
                        @click="exportExcel(__('Журнал обліку прийому хворих у стаціонар та відмов у госпіталізаціїї'))" />
                </div>
            </journal-list>
        </section>
    </page>
</template>
<script>
import JournalList from './patient-journal/List.vue';
import JournalFilter from './patient-journal/Filter.vue';
import ManageMixin from '@/mixins/manage';
import ProxyRepository from '@/repositories/proxy-repository';
import TreatmentCourseRepository from '@/repositories/treatment-course';
import * as journalGenerator from './patient-journal/generator';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        JournalList,
        JournalFilter,
    },
    data() {
        return {
            displayFilter: true,
            loading: false,
            reportRepository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repo = new TreatmentCourseRepository();
                return repo.fetchJournalList(filters, sort, scopes, page, limit);
            }),
            fileGenerator: journalGenerator,
        };
    },
    methods: {
        getDefaultFilters() {
            return {
                is_surgery: 1,
            };
        },
        patientAppointments() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-courses', params: {patientId: this.activeItem.patient_id}});
            window.open(routeData.href, '_blank');
        },
    },
}
</script>