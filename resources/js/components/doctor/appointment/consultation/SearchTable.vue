<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="auto"
        @loaded="loaded">
        <template
            slot="featured"
            slot-scope="props" >
            <featured 
                :model="props.rowData" 
                :featured-list="featuredList"
                @featured-changed="featuredChanged" />
        </template>
        <template
            slot="add-selection"
            slot-scope="props" >
            <div class="pr-20">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать специализацию') }}</a>
            </div>
        </template>
    </manage-table>
</template>
<script>
import SpecializationRepository from '@/repositories/specialization';
import ProxyRepository from '@/repositories/proxy-repository';
import Featured from './Featured.vue';
import DoctorConsultation from '@/models/patient/card/doctor-consultation';

export default {
    components: {
        Featured,
    },
    props: {
        filters: Object,
        appointment: Object,
        featuredList: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getList();
            }),
            fields: [
                {
                    name: 'featured',
                    title: '',
                    width: "30px",
                    dataClass: 'text-center',
                },
                {
                    name: 'specialization_name',
                    title: '',
                    dataClass: 'text-left',
                },
                {
                    name: 'add-selection',
                    title: '',
                    width: "200px",
                    dataClass: 'text-center',
                },
            ],
        };
    },
    methods: {
        getList() {
            let repo = new SpecializationRepository();
            return repo.fetchList(this.getSpecializationFilter()).then((response) => {
                return Promise.resolve({
                    rows: response.map(row => {
                        return new DoctorConsultation({
                            specialization_id: row.id,
                            specialization_name: row.value,
                        });
                    }),
                });
            });
        },
        getSpecializationFilter() {
            return _.onlyFilled(this.filters);
        },
        toggleSelection(row) {
            this.$emit('selection-changed', row);
        },
        loaded() {
            this.$emit('loaded');
        },
        featuredChanged(item) {
            this.$emit('featured-changed', item);
        },
    }
}
</script>