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
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
        <template
            slot="name"
            slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.name }}
                </span>
                <template v-if="props.rowData.use_detail_payments === true">
                    <svg-icon
                        name="info-alt"
                        class="icon-tiny icon-grey"
                        @click.stop="showDetails(props.rowData)" />
                </template>
            </div>
        </template>
        <template
            slot="clinics"
            slot-scope="props">
            <card-type-clinics
                :model="props.rowData"
                @edit-card-type="editCardType" />
        </template>
    </manage-table>
</template>

<script>
import DiscountCardTypeRepository from '@/repositories/discount-card-type';
import CardTypeDetails from './Details.vue';
import DetailButtonEdit from './DetailButtonEdit.vue';
import CardTypeClinics from './CardTypeClinics.vue';
import ClinicRepository from '@/repositories/clinic';

export default {
    components: {
        CardTypeClinics,
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new DiscountCardTypeRepository({
                limitClinics: this.$isAccessLimited('discount-card-types')
            }),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название типа карты'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'clinics',
                    title: __('Клиники'),
                    width: '20%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('discount-card-types')
                    }),
                    filterField: 'clinic',
                    dataClass: 'clinic-column',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'dont_use_for_patient',
                    title: __('Не выдавать пациентам'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'dont_use_for_patient',
                },
                {
                    name: 'show_card_in_patient_list',
                    title: __('Карта в списке пациентов'),
                    width: '15%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'show_card_in_patient_list',
                },
                {
                    name: 'use_card_number',
                    title: __('Использовать номер карты'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'use_card_number',
                },
                {
                    name: 'discount_percent',
                    sortField: 'discount_percent',
                    filterField: 'discount_percent',
                    filter: true,
                    title: __('Процент скидки'),
                    width: '10%',
                },
                {
                    name: 'number_kind',
                    title: __('Вид нумерации'),
                    width: '10%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'priority',
                    sortField: 'priority',
                    title: __('Приоритет'),
                    width: '10%',
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
        showDetails(cardType) {
            this.$modalComponent(CardTypeDetails, {
                cardType,
            }, {}, {
                header: __('Скидки по назначению платежа'),
                width: '600px',
                customClass: 'no-footer',
                headerAddon: {
                    component: DetailButtonEdit,
                    eventListeners: {
                        click: (dialog) => {
                            dialog.close();
                            this.editCardType(cardType);
                        }
                    }
                },
            });
        },
        editCardType(cardType) {
            this.$emit('edit-card-type', cardType);
        },
    },
}
</script>
