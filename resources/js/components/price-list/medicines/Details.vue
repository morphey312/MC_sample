<template>
	<manage-table
        ref="table"
        :fields="fields"
        :repository="repository">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
	props: {
		medicine: Object,
	},
	data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.getRows(),
                });
            }),
            fields: [
                {
                    name: 'firm_description',
                    title: __('Клиника'),
                    width: '35%',
                },
                {
                    name: 'description',
                    title: __('Склад'),
                    width: '35%',
                },
                {
                    name: 'rest',
                    title: __('Остаток'),
                    width: '10%',
                    dataClass: 'text-right',
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    width: '10%',
                    dataClass: 'text-right',
                },
                {
                    name: 'self_cost',
                    title: __('Себестоимость'),
                    width: '10%',
                    dataClass: 'text-right',
                },
            ],
        };
    },
    methods: {
        getRows() {
            if (this.$can('analysis-prices.create')) {
                return this.medicine.store_rests;
            }

            let clinics = this.$store.state.user.clinics;
            
            if (clinics.length === 0) {
                return [];
            }
            return this.medicine.store_rests.filter(store => {
                if (store.clinic && clinics.indexOf(store.clinic.id) !== -1) {
                    return true;
                }
                return false;
            });
        },
    },
}	
</script>