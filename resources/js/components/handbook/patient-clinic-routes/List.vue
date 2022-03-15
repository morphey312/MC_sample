<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
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
                @click.prevent="view(props.rowData.file_data.url, '', {}, $can('patient-clinic-routes.download'))">
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
import FileActionMixin from '@/mixins/file-action';
import PatientClinicRouteRepository from "@/repositories/patient/clinic-route";

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PatientClinicRouteRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    filter: true,
                    width: '60%',
                },
                {
                    name: 'specialization_names',
                    filterField: 'specialization',
                    title: __('Специализации'),
                    width: '40%',
                    filter: new SpecializationRepository(),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            scopes: [
                'file',
                'specializations'
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
    },
}
</script>
