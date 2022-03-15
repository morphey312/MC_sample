<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :flex-height="true"
        @header-filter-updated="syncFilters">
        <template
            slot="name"
            slot-scope="props">
            <a
                href="#"
                @click.prevent="view(props.rowData.file, props.rowData.url)">
                {{ props.rowData.file.name}}
            </a>
        </template>
    </manage-table>
</template>

<script>
import AppointmentDocumentRepository from "@/repositories/patient/appointment-document";
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import FileViewerHeader from "@/components/general/FileViewerHeader";
import FileViewer from "@/components/general/FileViewer";
import Echo from "@/components/doctor/appointment/patient-history/modals/Echo";

export default {
    name: "ListAppointment",
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new AppointmentDocumentRepository(),
            filters: {
                patient: this.patient.id,
            },
            fields: [
                {
                    name: 'name',
                    filterField: 'name',
                    title: __('Имя документа'),
                    filter: true,
                },
                {
                    name: 'created_at',
                    sortField: 'created_at',
                    title: __('Дата'),
                    width: '10%',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'assigner_name',
                    sortField: 'assigner',
                    title: __('Сформировал'),
                    width: '15%',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({
                ...this.filters,
                ...updates,
            });
        },
        getTable() {
            return this.$refs.table;
        },
        view(file, url) {
            this.$modalComponent(FileViewer, {
                file: file,
                url: url,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                width: '835px',
                customClass: 'no-footer',
                headerAddon: {
                    component: FileViewerHeader,
                    props:{
                        showDownload: true,
                        showPrint: true
                    },
                    eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                        downloadFile(dialog){
                            dialog.getTopComponent().download(file.name);
                        }
                    },
                }

            });
        }
    },
}
</script>

<style scoped>

</style>
