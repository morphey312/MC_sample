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
        <template slot="spacer">
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import ProtocolTemplateRepository from '@/repositories/patient/card/protocol-template';
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        filters: Object
    },
    data() {
        return {
            repository: new ProtocolTemplateRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '40%',
                    filter: true,
                },
                {
                    name: 'specialization_name',
                    filterField: 'specialization',
                    sortField: 'specialization',
                    title: __('Специализация'),
                    width: '30%',
                    filter: new SpecializationRepository(),
                },
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '30%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('protocol-templates'),
                    }),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filterProps: {
                        multiple: true,
                        limit: 50,
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
