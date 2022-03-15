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
            repository: new ActionLogRepository({filters: {patient_prepayment: this.id}}),
            attributes: {
                patient_id: __('Пациент'),
                service_id: __('Услуга'),
                clinic_id: __('Клиника'),
                amount: __('Сумма'),
                used: {
                    label: __('Использована'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
            },
        };
    },
};
</script>