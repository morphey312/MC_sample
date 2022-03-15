<template>
    <section
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')"
        class="p-0 shrinkable-tabs">
        <drawer :open="displayFilterProp">
            <section class="grey filter">
                <patient-filter
                    ref="patientFilter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            class="grey-cap p-20 shrinkable">
            <patient-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @header-filter-updated="syncFilters"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('patients')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="createPatientAndSync" />
                    <form-button
                        v-if="$canUpdate('patients')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="editPatient" />
                    <form-button
                        v-if="$can('patient-cabinet.access')"
                        :disabled="activeItem === null"
                        :text="__('Кабинет пациента')"
                        icon="catalogue"
                        @click="showCabinet" />
                    <form-button
                        v-if="$can('action-logs.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                    <form-button
                        v-if="$can('patients.export')"
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('Пациенты'))" />
                </div>
            </patient-list>
        </section>
        <section
            v-else
            class="grey-cap shrinkable" style="padding-top: 80px">
            <wait-search-placeholder
                :create-text="__('создайте пациента')"
                :can-create="$canCreate('patients')"
                @create="createPatientAndSync" />
        </section>
    </section>
</template>

<script>
import PatientFilter from './patient/Filter.vue';
import PatientList from './patient/List.vue';
import PatientCreateMixin from './mixins/patient-create';
import ManageMixin from '@/mixins/manage';
import PatientLog from '@/components/action-log/Patient.vue';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import PatientRepository from '@/repositories/patient';
import * as patientGenerator from './generators/patients';
import EhealthSwitcher from "@/components/ehealth/patient/EhealthSwitcher.vue";

export default {
    mixins: [
        PatientCreateMixin,
        ManageMixin,
        ExportXLSXMixin,
    ],
    components: {
        PatientFilter,
        PatientList,
    },
    props: {
        displayFilterProp: Boolean,
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
        editPatient() {
            this.displayEditPatientForm(this.activeItem.id,(patient) => {
                this.lastActiveItemId = patient.id;
                this.refresh();
            }, null,{},{
                component: EhealthSwitcher,
                eventListeners: {
                    ehealthRegistration: (dialog) => {
                        dialog.getTopComponent().bindEhealth();
                    },
                    editEhealthPatient: (dialog, data) => {
                        dialog.getTopComponent().editEhealthPatient(data)
                    }
                },
            }, {
                setEhealthPatient: (dialog, data) => {
                    dialog.getTopHeaderAddon().setEhealthPatient(data)
                }
            });
        },
        createPatientAndSync() {
            this.createPatient({}, (patient) => {
                this.$nextTick(() => {
                    this.getFilter().sync({
                        firstname: patient.firstname,
                        lastname: patient.lastname,
                        middlename: patient.middlename,
                    });
                });
            });
        },
        refresh() {
            if (this.displayTable) {
                this.getManageTable().refresh();
            }
        },
        getFilter() {
            return this.$refs.patientFilter;
        },
        showCabinet() {
            this.$router.push({name: 'patient-cabinet-info', params: {patientId: this.activeItem.id}});
        },
        showLog() {
            this.$modalComponent(PatientLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения данных пациента'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
