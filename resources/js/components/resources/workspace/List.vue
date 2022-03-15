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
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import WorkspaceRepository from '@/repositories/workspace';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';

export default {
    props: {
        filters: Object,
    },
    data() {
        let specializationRepo = new SpecializationRepository();

        return {
            repository: new WorkspaceRepository({
                limitClinics: this.$isAccessLimited('workspaces')
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
                    name: 'workspace_clinics',
                    title: __('Клиника'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, 'clinic_name');
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('workspaces')
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },

                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: specializationRepo,
                    filterField: 'specialization',
                },
                {
                    name: 'sip_number',
                    title: __('Номер SIP'),
                    width: '20%',
                    filter: true,
                    filterField: 'sipNumber',
                },
                {
                    name: 'has_day_sheet',
                    title: __('Есть табель'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'hasDaySheet',
                },
                {
                    name: 'is_active',
                    title: __('Активный'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                }
            ],
            specializationRepo,
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
        };
    },
    watch: {
        ['filters.clinic']: {
            immediate: true,
            handler() {
                this.specializationRepo.setFilters(this.getSpecializationFilters());
            }
        },
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
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
            });
        },
    }
}
</script>
