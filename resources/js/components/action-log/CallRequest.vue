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
    },
    data() {
        return {
            repository: new ActionLogRepository({filters: {call_request: this.id}}),
            attributes: {
                recall_from: {
                    label: __('Начало периода прозвона'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                recall_to: {
                    label: __('Окончание периода прозвона'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                status: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('call_request_status', val);
                    }
                },
                comment: __('Комментарий'),
                comment_canceled: __('Комментарий к отмене'),
                call_request_purpose_id: __('Цель прозвона'),
                patient_id: __('Пациент'),
                clinic_id: __('Клиника'),
                specialization_id: __('Специализация'),
            },
        };
    },
};
</script>