import PatientDocumentRepository from "@/repositories/patient-document";
import Blank from '../Blank.vue';
import HeaderAddon from "../HeaderAddon.vue";
import LogPrintMixin from './log-print';

export default {
    mixins: [
        LogPrintMixin,
    ],
    props: {
        appointment: Object,
        clinicRequisites: Object,
        featuredList: {
            type: Array,
            default: () => [],
        },
        doctorSpecializationList: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            repository: new PatientDocumentRepository(),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                    filterField: 'name',
                    filter: true,
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: "20%",
                    filterField: 'specialization',
                    filter: this.doctorSpecializationList,
                    filterProps: {
                        multiple: true,
                    },
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'fill_out',
                    title: __('Заполнить и печатать'),
                    width: "150px",
                },
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        loaded() {
            this.$emit('loaded');
        },
        cancel() {
            this.$emit('cancel');
        },
        printHandle(patientDocument) {
            this.logPrint(patientDocument);
        },
        fillOut(file) {
            this.$modalComponent(Blank, {
                appointment: this.appointment,
                file: file,
                clinicRequisites: this.clinicRequisites,
            }, {
                cancel: (dialog) => {
                    this.cancel();
                },
                printed: (dialog, patientDocument) => {
                    this.logPrint(patientDocument);
                },
            }, {
                width: '1150px',
                headerAddon: {
                    component: HeaderAddon,
                        eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                    },
                }
            });
        },
    },
}