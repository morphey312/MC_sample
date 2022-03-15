<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
        :flex-height="true"
        @loaded="loaded"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template slot="numbering_kinds" slot-scope="props">
            {{ getNumTypes(props.rowData.clinics) }}
        </template>
        <template slot="prefix" slot-scope="props">
            {{ getPrefix(props.rowData.clinics) }}
        </template>
        <template slot="suffix" slot-scope="props">
            {{ getSuffix(props.rowData.clinics) }}
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import NumberingKindRepository from '@/repositories/discount-card-type/numbering-kind';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new NumberingKindRepository({
                limitClinics: this.$isAccessLimited('card-numbering-kinds')
            }),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'unique',
                    title: __('Номер карты уникальный'),
                    width: '8%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'numbering_kinds',
                    title: __('Типы нумерации'),
                    width: '15%',
                },
                {
                    name: 'prefix',
                    title: __('Префикс'),
                    width: '20%',
                },
                {
                    name: 'suffix',
                    title: __('Суффикс'),
                    width: '20%',
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '40%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
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
        getNumTypes(clinics){
            let types = clinics.map((clinic) => {
                return  this.$handbook.getOption('card_numbering_type', clinic.numbering_type);
            });
            return this.$formatter.listFormat(_.uniq(types));
        },
        getPrefix(clinics){
            let prefixes = clinics.map((clinic) => {
                return  clinic.prefix;
            });
            return this.$formatter.listFormat(_.uniq(prefixes));
        },
        getSuffix(clinics){
            let suffixes = clinics.map((clinic) => {
                return  clinic.suffix;
            });
            return this.$formatter.listFormat(_.uniq(suffixes));
        }
    },
}
</script>
