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
            repository: new ActionLogRepository({filters: {patient_source: this.id}}),
            attributes: {
                name: __('Название'),
                employee_id: __('Сотрудник для перенаправлений'),
                clinics: {
                    label: __('Клиники'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                is_active: {
                    label: __('Активный'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_collective_form: {
                    label: __('Собирательный образ для вида рекламы'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                show_in_appointment: {
                    label: __('Показывать имя источника в записи'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                media_type: {
                    label: __('Вид рекламы'),
                    formatter: (val) => {
                        return this.$handbook.getOption('media_type', val);
                    }
                },
            },
        };
    },
};
</script>