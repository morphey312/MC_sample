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
            repository: new ActionLogRepository({filters: {call: this.id}}),
            attributes: {
                comment: __('Примечание'),
                time: __('Время'),
                date: {
                    label: __('Дата'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                is_first: {
                    label: __('Первичный'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                call_result_id: __('Результат звонка'),
                clinic_id: __('Клиника'),
                employee_id: __('Оператор'),
                patient_id: __('Пациент'),
                contact: __('Контакт'),
                specialization_id: __('Специализация'),
                call_request_id: __('Заявка на прозвон'),
                call_delete_reason_id: __('Причина удаления звонка'),
                delete_reason_comment: __('Примечание к удалению'),
            },
        };
    },
};
</script>