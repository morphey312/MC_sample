<script>
import DefaultLog from './Default.vue';
import ActionLogRepository from '@/repositories/action-log';
import withClinicRelation from './mixins/withClinicRelation';

export default {
    extends: DefaultLog,
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            repository: new ActionLogRepository({filters: {payment_method: this.id}}),
            attributes: {
                fiscal: {
                    label: __('Фискальная оплата'),
                    formatter: (val) => {
                        return this.getFiscalFormat(val);
                    }
                },
                clinics: {
                    label: __('Клиники'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                name: __('Метод оплаты'),
                is_enabled: {
                    label: __('Активная'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                pc_payment: {
                    label: __('Для оплат с ЛК'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                online_payment: {
                    label: __('Онлайн оплата'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                change_payment_date: {
                    label: __('Возможность изменения даты платежа'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                use_cash: {
                    label: __('Наличные средства'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
            },
        };
    },
    methods: {
        getFiscalFormat(val) {
            let clinics = [];
            val.forEach(clinic => clinics.push(clinic.split('|')));
            clinics.forEach(clinic => {
                if(clinic[1] === '1') {
                    clinic[1] = __('Да');
                }else {
                    clinic[1] = __('Нет');
                }
            });
            return this.$formatter.listFormat(clinics.map(clinic => `${clinic[0]} Фискальная оплата «${clinic[1]}»`));
        }
    }
};
</script>
