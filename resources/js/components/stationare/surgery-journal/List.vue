<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :scopes="scopes"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot-scope="props"
            slot="patient_full_name">
            <a 
                v-if="protocolPresents(props.rowData)"
                href="#" 
                @click.prevent="showOperationProtocol(props.rowData)">
                {{ props.rowData.patient.full_name }}
            </a>
            <template v-else>
                {{ props.rowData.patient.full_name }}
            </template>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import AppointmentRepository from '@/repositories/appointment';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import HeaderAddon from '@/components/patients/cabinet/treatment-courses/course-templates/HeaderAddon.vue';
import FileViewer from '@/components/general/FileViewer.vue';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repo = new AppointmentRepository();
                return repo.fetchSurgeryList(filters, sort, scopes, page, limit);
            }),
            fields: [
                {
                    name: 'treatment_course.number',
                    title: __('Номер'),
                    dataClass: 'no-dash',
                    width: '70px',
                },
                {
                    name: 'date',
                    sortField: 'date',
                    title: __('Дата записи'),
                    width: '110px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD/MM/YYYY');
                    },
                    filter: DateHeaderFilter,
                    filterField: 'date',
                },
                {
                    name: 'patient_full_name',
                    title: __('ФИО'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
                {
                    name: 'surgeonist',
                    title: __('Хирург'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
                {
                    name: 'anesthesiologist',
                    title: __('Анестезиолог'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
                {
                    name: 'diagnoses',
                    title: __('Диагноз до операции'),
                    dataClass: 'no-dash',
                    width: '120px',
                    sortField: 'diagnosis',
                    filter: true,
                    filterField: 'diagnosis',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'appointment_services',
                    title: __('Вид анестезии'),
                    dataClass: 'no-dash',
                    width: '200px',
                    formatter: (val) => {
                        if (!val || val.length === 0) {
                            return '';
                        }
                        return this.$formatter.listFormat(val.filter((service) => {
                            return service.service_mark === CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_ANESTHESIA;
                        }), 'name');
                    },
                },
                {
                    name: 'diagnoses_after',
                    title: __('Диагноз после операции'),
                    dataClass: 'no-dash',
                    width: '120px',
                    sortField: 'diagnosis',
                    filter: true,
                    filterField: 'diagnosis',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'card_only_number',
                    filterField: 'patient_card_number',
                    title: __('№ карты'),
                    width: '90px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'assistant',
                    title: __('Ассистенты'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
                {
                    name: 'nurse',
                    title: __('Операционная медсестра'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
            ],
            initialSortOrder: [
                {field: 'date_start', direction: 'asc'},
            ],
            scopes: [
                'patient_card_specialization',
                'surgery_employees',
                'diagnosis',
                'service_destination',
                'surgery_course',
            ]
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        protocolPresents(model) {
            return model.treatment_course_documents 
                && model.treatment_course_documents.findIndex(doc => doc.type === CONSTANTS.STATIONAR_MOZ_BLANK.DISCHARGE_DIARY) !== -1;
        },
        showOperationProtocol(model) {
            let file = model.treatment_course_documents.find(doc => doc.type === CONSTANTS.STATIONAR_MOZ_BLANK.DISCHARGE_DIARY);
            let url = file.attachments_data[0].url;
            let title = model.patient.full_name;
            let header = title + " №" + model.card_only_number;
            this.$modalComponent(FileViewer, {url}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                print: (dialog, data) => {
                    if (this.printHandle){
                        this.printHandle(data);
                    }
                    dialog.$emit('print', data);
                },
            }, {
                header,
                width: '1100px',
                headerAddon: {
                    component: HeaderAddon,
                    props: {
                        initialAllowed: true,
                        document: file,
                    },
                    eventListeners: {
                        download: (dialog) => {
                            dialog.getTopComponent().download(header);
                        },
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                    },
                }
            });
        },
    }
}
</script>
