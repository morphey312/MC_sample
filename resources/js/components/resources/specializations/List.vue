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
        @header-filter-updated="syncFilters" >
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
        <template
            slot="name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.name }}
                </span>
                <svg-icon
                    v-if="props.rowData.clinics && props.rowData.clinics.length > 0"
                    name="info-alt"
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
    </manage-table>
</template>

<script>
import SpecializationRepository from '@/repositories/specialization';
import SpecializationDetails from './Details.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('specializations')
            }),
            fields: [
                {
                    name: 'name_i18n',
                    sortField: 'name_i18n',
                    title: __('Специализация'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'short_name',
                    sortField: 'shortName',
                    title: __('Краткое название'),
                    width: "20%",
                    filter: true,
                },
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус'),
                    width: "15%",
                    formatter: (value) => {
                        return this.$formatter.fromHandbook('active_status', value);
                    },
                    filter: this.$handbook.getOptions('active_status'),
                    filterField: 'status',
                },
                {
                    name: 'clinics',
                    title: __('Клиника'),
                    width: "35%",
                    formatter: (value) => {
                        return this.$formatter.listFormat(_.sortBy(value, 'name'), 'name');
                    },
                },
                {
                    name: 'online_appointment',
                    title: __('Возможность записи в листы из ЛК'),
                    dataClass: 'no-dash',
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
            ],
            initialSortOrder: [
                {field: 'name_i18n', direction: 'asc'},
            ],
            scopes: [
                'clinics',
                'adjacents',
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
        showDetails(specialization) {
            this.$modalComponent(SpecializationDetails, {
                specialization,
            }, {}, {
                header: specialization.name,
                width: '1000px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>
