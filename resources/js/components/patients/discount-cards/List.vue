<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @loaded="loaded"
        @selection-changed="selectionChanged" />
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                    rows: this.getCardList(),
                });
            }),
            fields: [
                {
                    name: 'type.name',
                    title: __('Тип карты'),
                    dataClass: 'no-dash',
                }, 
                {
                    name: 'type.discount_percent',
                    title: __('% скидки'),
                    dataClass: 'no-dash text-right',
                },
                {
                    name: 'card_number',
                    title: __('№ карты'),
                    dataClass: 'no-dash', 
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника где выдана'),
                },
                {
                    name: 'issued',
                    title: __('Дата выдачи'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'type.dont_use_for_patient',
                    title: __('Не используется тип'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'patients',
                    title: __('Не действует'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(this.isDisabled(value), '<span class="check-yes" />');
                    },
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    dataClass: 'no-dash',
                },
            ],
        }
    },
    mounted() {
        this.$watch('patient.issued_discount_cards', () => {
            this.$refs.table.refresh();
        }, { deep: true });
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        getCardList() {
            return this.patient.issued_discount_cards;
        },
        isDisabled(list) {
            let patient = list.find(item => item.patient_id == this.patient.id);
            return patient ? patient.disabled : false;
        },
    },
}
</script>