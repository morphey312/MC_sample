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
        model: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.model.clinics,
                });
            }),
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '50%',
                },
                {
                    name: 'payment_method_name',
                    title: __('Вид оплаты'),
                    width: '50%',
                },
            ],
        };
    },
}
</script>