<script>
import DefaultLog from '@/components/action-log/Default.vue';
import ActionLogRepository from '@/repositories/action-log';

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
            repository: new ActionLogRepository({filters: {insurance_act: this.id}}),
            attributes: {
                insurance_company_id: __('Страховая'),
                clinic_id: __('Клиника'),
                amount: __('Сумма'),
                number: __('Номер'),
                comment: __('Комментарий'),
                created: {
                    label: __('Создан'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val.date);
                    },
                },
                payed: {
                    label: __('Разнос платежей'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val.date);
                    },
                },
                status_changed: {
                    label: __('Изменение статуса'),
                    formatter: (val) => {
                        return this.$formatter.fromHandbook('insurance_act_status', val);
                    },
                },
                printed: {
                    label: __('Распечатано'),
                    formatter: (val) => {
                        return this.$formatter.boolToString(val);
                    }
                },
            },
        };
    },
};
</script>