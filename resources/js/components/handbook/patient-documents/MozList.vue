<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="name"
            slot-scope="props">
            <a
                href="#"
                @click.prevent="view(props.rowData.file_data.url)">
                {{ props.rowData.name }}
            </a>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import FileActionMixin from '@/mixins/file-action';
import PatientDocumentRepository from "@/repositories/patient-document";
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PatientDocumentRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    filter: true,
                },
                {
                    name: 'name_ua',
                    sortField: 'name_ua',
                    title: __('Название (укр.)'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'specialization_names',
                    filterField: 'specialization',
                    title: __('Специализации'),
                    width: '20%',
                    filter: new SpecializationRepository(),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '15%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('patient-documents'),
                    }),
                    filterProps: {
                        limit: 50
                    },
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
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
    },
}
</script>
