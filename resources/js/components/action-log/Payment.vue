<script>
import DefaultLog from './Default.vue';
import ActionLogRepository from '@/repositories/action-log';

export default {
    extends: DefaultLog,
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
        category: {
            type: String,
        },
    },
    data() {
        return {
            repository: new ActionLogRepository({
                filters: {
                    payment: this.id,
                },
            }),
            attributes: {
                amount: __('Сумма'),
                payed_amount: __('Оплачено'),
                discount: __('Скидка'),
                cashbox_id: __('Касса'),
                service_id: __('Услуга'),
                doctor_id: __('Врач'),
                patient_id: __('Пациент'),
                appointment_id: __('Запись на прием'),
                payment_destination_id: __('Назначение платежа'),
                is_deleted: {
                    label: __('Удален'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    },
                },
                is_deposit: {
                    label: __('Аванс'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    },
                },
                from_deposit: {
                    label: __('С аванса'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    },
                },
                created_at: {
                    label: __('Дата патежа'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
            },
        };
    },
};
</script>