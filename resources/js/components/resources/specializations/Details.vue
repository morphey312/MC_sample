<template>
    <manage-table
        v-if="!loading"
        ref="table"
        :fields="fields"
        :repository="repository">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import MoneyRecieversRepository from '@/repositories/clinic/money-reciever';

export default {
    props: {
        specialization: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.prepareRows(this.specialization.clinics),
                });
            }),
            moneyRecievers: [],
            fields: [
                {
                    name: 'name',
                    title: __('Клиника'),
                    width: '20%',
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: "20%",
                    formatter: (value) => {
                        return this.$formatter.fromHandbook('active_status', value);
                    },
                },
                {
                    name: 'first_patient_appointment_limit',
                    title: __('Минимальное количество записей'),
                    width: '20%',
                },
                {
                    name: 'days_since_last_visit',
                    title: __('Давность посещения'),
                    width: '20%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'money_reciever',
                    title: __('Получатель денег'),
                    width: '20%',
                },
            ],
            loading: true,
        };
    },
    beforeMount() {
        this.getRecievers();
    },
    methods: {
        getRecievers() {
            this.loading = true;
            let reciever = new MoneyRecieversRepository()
            reciever.fetchList().then(response => {
                this.moneyRecievers = response;
                this.loading = false;
            });
        },
        prepareRows(clinics) {
            return clinics.map(clinic => {
                clinic.money_reciever = this.getRecieverName(clinic.money_reciever_id);
                return clinic;
            });
        },
        getRecieverName(recieverId) {
            let reciever = this.moneyRecievers.find(item => item.id == recieverId);
            return reciever ? reciever.value : '';
        },
    }
}
</script>