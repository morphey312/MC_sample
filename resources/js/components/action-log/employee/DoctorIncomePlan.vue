<script>
import DefaultLog from '../Default.vue';
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
            repository: new ActionLogRepository({filters: {doctor_income_plan: this.id}}),
            attributes: {
                employee: __('Название анализа'),
                clinic_id: __('Клиника'),
                specialization_id: __('Специализация'),
                plan_service_mark: __('Опция специалиста'),
                year: __('Год'),
                january: __('Январь'),
                february: __('Февраль'),
                march: __('Март'),
                april: __('Апрель'),
                may: __('Май'),
                june: __('Июнь'),
                july: __('Июль'),
                august: __('Август'),
                september: __('Сентябрь'),
                october: __('Октябрь'),
                november: __('Ноябрь'),
                december: __('Декабрь'),
            },
        };
    },
};
</script>