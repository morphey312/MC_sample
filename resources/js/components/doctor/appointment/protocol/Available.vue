<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :scopes="scopes"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :show-table-settings="false"
        :enable-pagination="false"
        table-height="auto"
        @header-filter-updated="syncFilters">
        <template
            slot="featured"
            slot-scope="props" >
            <featured 
                :model="props.rowData" 
                :featured-list="featuredList"
                @featured-changed="featuredChanged" />
        </template>
        <template 
            slot="add"
            slot-scope="props">
            <a 
                href="#"
                @click.prevent="add(props.rowData)">
                {{ __('Выбрать услугу') }}
            </a>
        </template>
    </manage-table>
</template>

<script>
import ServiceRepository from '@/repositories/service';
import ProxyRepository from '@/repositories/proxy-repository';
import Featured from './Featured.vue';
import CastServiceMixin from './mixin/cast-service';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        CastServiceMixin,
    ],
    components: {
        Featured,
    },
    props: {
        specialization: [Number, String],
        skipId: Array,
        appointment: Object,
        featuredList: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new ServiceRepository();
                return repository.fetch(filters, sort, scopes, page, limit).then((result) => {
                    return {
                        rows: this.castServiceRows(result.rows),
                        pagination: result.pagination,
                    }
                });
            }),
            fields: [
                {
                    name: 'featured',
                    title: '',
                    width: "30px",
                    dataClass: 'text-center',
                },
                {
                    name: 'service.name',
                    sortField: 'name',
                    filterField: 'name',
                    title: __('Название услуги'),
                    filter: true,
                },
                {
                    name: 'service.protocol_templates',
                    filterField: 'protocol_template',
                    title: __('Название протокола'),
                    width: '200px',
                    filter: true,
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, 'name');
                    },
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн.'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '90px',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '90px',
                },
                {
                    name: 'costWithDiscount',
                    title: __('С учетом скидки, грн.'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '90px',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'add',
                    title: ' ',
                    width: '110px',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {
                specialization: this.specialization,
                has_protocol_clinic: this.appointment.clinic_id,
                skip_id: this.skipId,
                clinic: this.appointment.clinic_id,
                base: false,
                disabled: false,
                hasPrice: {
                    clinic: this.appointment.clinic_id,
                    from: this.appointment.date,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
            },
            scopes: ['protocol_templates', 'prices_for_query'],
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        add(row) {
            this.$confirm(__('Добавить «{name}» в запись на прием?', {name: row.service.name}), () => {
                this.appointment.addService({
                    service_id: row.service.id,
                    cost: row.costWithDiscount,
                    quantity: 1,
                    discount: row.discount,
                    price_id: row.priceId, 
                }).then(() => {
                    this.$info(__('Услуга успешно добавлена к записи на прием'));
                    this.$emit('added');
                });
            });
        },
        featuredChanged(item) {
            this.$emit('featured-changed', item);
        },
    },
    watch: {
        skipId(val) {
            this.syncFilters({skip_id: val});
        } 
    },
};
</script>