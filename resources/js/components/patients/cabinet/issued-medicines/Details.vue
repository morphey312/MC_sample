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
                    rows: this.medicine.store_rests,
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
                    width: '30%',
                    dataClass: 'text-right',
                },
            ],
        };
    },
}	
</script>