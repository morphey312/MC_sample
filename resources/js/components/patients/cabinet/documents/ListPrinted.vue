<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :flex-height="true"
        @header-filter-updated="syncFilters"
    >
        <template
            slot="name"
            slot-scope="props"
        >
            <a
                v-if="props.rowData.file"
                href="#"
                @click.prevent="view(props.rowData.file.url)"
            >
                {{ props.rowData.file.name}}
            </a>
            <a v-else
               href="#"
               @click.prevent="showPrintedDocument(props.rowData)"
            >
                {{ __('Консультативное заключение') }}
            </a>
        </template>
    </manage-table>

</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import CardRecordRepository from '@/repositories/patient/card/record';
import EmployeeRepository from '@/repositories/employee';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS  from '@/constants';
import FileActionMixin from '@/mixins/file-action';
import Echo from "../../../doctor/appointment/patient-history/modals/Echo";
import FileViewerHeader from "../../../general/FileViewerHeader";

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, page, limit}) => {
                let cards = this.patient.cards;
                let repository = new CardRecordRepository();
                if (cards.length === 0) {
                    return repository.emptyData();
                }
                return repository.fetch({
                    ...filters,
                    specialization_card: cards.map(c => c.id),
                }, sort, ['doctor'], page, limit);
            }),
            filters: {
                specialization_card: this.patient.cards.map(card => card.id),
                type: [CONSTANTS.CARD_RECORD.TYPE.PRINTED_DOCUMENT],
            },
            fields: [
                {
                    name: 'name',
                    filterField: 'name',
                    title: __('Имя документа'),
                    filter: true,
                },
                {
                    name: 'date',
                    sortField: 'date',
                    filterField: 'date',
                    title: __('Дата'),
                    width: '10%',
                    filter: DateHeaderFilter,
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {
                    name: 'doctor.full_name',
                    filterField: 'doctor',
                    title: __('Врач'),
                    width: '15%',
                    filter: new EmployeeRepository({
                        filters: {
                            positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                            has_patient_appointment: this.patient.id,
                        },
                    }),
                },
            ],
            initialSortOrder: [
                {field: 'date', direction: 'desc'},
            ],
        };
    },
    methods: {
        showPrintedDocument(record){
            let header = __('Просмотр распечатанного заключения ({date})', {date: this.$formatter.datetimeFormat(record.date)});

            this.$modalComponent(Echo, {
                record: record
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header,
                width: '835px',
                customClass: 'no-footer',
                headerAddon: {
                    component: FileViewerHeader,
                    props:{
                        showDownload: false
                    },
                    eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                        downloadFile(dialog){
                            dialog.getTopComponent().download();
                        }
                    },
                }

            });
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({
                ...this.filters,
                ...updates,
            });
        },
        getTable() {
            return this.$refs.table;
        },
    },
};
</script>
