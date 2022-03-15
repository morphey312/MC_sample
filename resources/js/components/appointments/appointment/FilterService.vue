<template>
    <section class="grey">
        <form-row name="services">
            <transfer-table
                ref="transfer"
                key="services"
                :items="service_list"
                :items-fields="itemFields"
                :fields="serviceFields"
                :initial-filters="{'itemdata.specialization.id': filter.specialization}"
                v-model="filter.services"
                :left-title="__('Услуги')"
                left-width="340px"
                :right-title="__('Выбранные услуги')"
                right-width="340px"
                height="240px"
                :emptySelectionMessage="placeholder">
            </transfer-table>
        </form-row>
    </section>
</template>
<script>
import SpecializationRepository from '@/repositories/specialization';
import ServiceRepository from '@/repositories/service';
import Service from '@/models/service';

export default {
    props: {
        filter: Object,
    },
    data() {
        return {
            service_list: [],
            placeholder: this.getPlaceholder(),
            itemFields: [
                {
                    name: 'itemdata.specialization.name',
                    title: __('Специализация'),
                    width: '136px',
                    editable: false,
                    filter: new SpecializationRepository({filters: this.getSpecializationFilters()}),
                    filterField: 'itemdata.specialization.id',
                    filterProps: {
                        multiple: true,
                    },
                },
            ],
            serviceFields: [
                {
                    name: 'itemdata.specialization.name',
                    title: __('Специализация'),
                    width: '136px',
                    editable: false,
                },
            ],
        }
    },
    mounted() {
        this.getServices();
        this.$watch('$refs.transfer.filters', (val) => {
                this.getServices();
            },
            { deep: true }
        );
    },
    watch: {
        ['filter.specialization'](val) {
            this.$nextTick(() => {
                this.getTransfer().applyFilter({'itemdata.specialization.id': val});
            });
        },
        ['filter.clinic']() {
            this.getServices();
        },
    },
    methods: {
        getServices() {
            let transferFilter = this.getTransferFilterValues();
            if (_.isEmpty(transferFilter)) {
                this.service_list = [];
                return;    
            }
            let service = new ServiceRepository();
            service.fetchList(this.getServiceFilters(transferFilter)).then((response) => {
                let service_list = response.map((row) => {
                    return  {
                        id: row.id,
                        value: row.value,
                        itemdata: new Service(row),   
                    }
                });
                this.updateServiceList(service_list);
            });
        },
        updateServiceList(service_list) {
            let tempList = [...this.service_list];
            service_list.forEach(service => {
                let index = this.service_list.findIndex(item => {
                    return item.id == service.id;
                });
                if (index == -1) {
                    tempList.push(service);
                }
            });
            this.service_list = tempList;
        },
        getTransfer() {
            return this.$refs.transfer;
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
            });
        },
        getTransferFilterValues() {
            let filter = this.getTransfer().filters;
            return _.onlyFilled({
                name: filter.value,
                specialization: filter['itemdata.specialization.id'].length != 0 
                                ? filter['itemdata.specialization.id'] 
                                : null,
            });
        },
        getServiceFilters(filter = {}) {
            return _.onlyFilled({
                specialization: this.filter.specialization,
                clinic: this.filter.clinic,
                disabled: false,
                ...filter,
            });
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Добавить услуги слева'));
        },
    },
}
</script>
