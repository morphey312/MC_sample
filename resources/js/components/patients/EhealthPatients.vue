`<template>
    <section
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')"
        class="p-0 shrinkable-tabs">
        <drawer :open="displayFilter">
            <section class="grey filter">
                <ehealth-patient-filter
                    ref="patientEhealthFilter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            class="grey-cap p-20 shrinkable">
            <ehealth-patient-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @header-filter-updated="syncFilters"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <!--                <form-button-->
                    <!--                    v-if="$canCreate('patients')"-->
                    <!--                    :text="__('Добавить')"-->
                    <!--                    icon="plus"-->
                    <!--                    @click="createPatientAndSync" />-->
<!--                    <form-button-->
<!--                        :text="__('Добавить')"-->
<!--                        icon="plus"-->
<!--                        @click="" />-->
<!--                    <form-button-->
<!--                        :disabled="activeItem === null"-->
<!--                        :text="__('Редактировать')"-->
<!--                        icon="edit"-->
<!--                        @click="" />-->
<!--                    <form-button-->
<!--                        v-if="$can('patient-cabinet.access')"-->
<!--                        :disabled="activeItem === null"-->
<!--                        :text="__('Кабинет пациента')"-->
<!--                        icon="catalogue"-->
<!--                        @click="" />-->
<!--                    <form-button-->
<!--                        v-if="$can('action-logs.access')"-->
<!--                        :disabled="activeItem === null"-->
<!--                        :text="__('Операции')"-->
<!--                        icon="menu-marketing"-->
<!--                        @click="" />-->
<!--                    <form-button-->
<!--                        v-if="$can('patients.export')"-->
<!--                        :text="__('Экспорт в Excel')"-->
<!--                        icon="download"-->
<!--                        @click="" />-->
<!--                    <form-button-->
<!--                        :text="__('Сохранить в МЦ+')"-->
<!--                        icon="download"-->
<!--                        @click="" />-->
                </div>
            </ehealth-patient-list>
        </section>
    </section>
</template>

<script>
import EhealthPatientFilter from '../ehealth/patient/Filter';
import EhealthPatientList from '../ehealth/patient/List.vue';
import PatientCreateMixin from './mixins/patient-create';
import ManageMixin from '@/mixins/manage';
import PatientLog from '@/components/action-log/Patient.vue';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import PatientRepository from '@/repositories/patient';
import * as patientGenerator from './generators/patients';

export default {
    mixins: [
        PatientCreateMixin,
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        EhealthPatientFilter,
        EhealthPatientList,
    },
    props: {
        displayFilter: Boolean,
        default: true,
    },
    data() {
        return {
            displayTable: false,
            reportRepository: new PatientRepository(),
            loading: false,
            fileGenerator: patientGenerator,
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
    },
}
</script>
