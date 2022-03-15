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
        <template
            slot="specialization_name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.specialization_name }}
                </span>
                <svg-icon 
                    name="info-alt" 
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
    </manage-table>
</template>

<script>
import AppointmentLimitationRepository from '@/repositories/appointment/limitation';
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import LimitationDetails from './Details.vue';
import DetailButtonEdit from './DetailButtonEdit.vue';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new AppointmentLimitationRepository(),
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    sortField: 'clinic',
                    width: '20%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('limitations'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'specialization_name',
                    title: __('Специализация'),
                    sortField: 'specialization',
                    width: '20%',
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('limitations'),
                    }),
                    filterField: 'specialization',
                },
                {
                    name: 'date_from',
                    title: __('Дата начала ограничения'),
                    sortField: 'dateFrom',
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'dateFrom',
                },
                {
                    name: 'date_to',
                    title: __('Дата окончания ограничения'),
                    sortField: 'dateTo',
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'dateTo',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'limitation',
                    title: __('Мин. количество записей в отделении'),
                    width: '20%',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'date_from', direction: 'desc'},
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
        showDetails(limitation) {
            this.$modalComponent(LimitationDetails, {
                limitation,
            }, {}, {
                header: __('Ограничение по специализации'),
                width: '770px',
                customClass: 'no-footer',
                headerAddon: {
                    component: DetailButtonEdit,
                    eventListeners: {
                        click: (dialog) => {
                            dialog.close();
                            this.$emit('edit-limitation', limitation);
                        }
                    }
                },
            });
        },
    },
}
</script>