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
            repository: new ActionLogRepository({filters: {analysis: this.id}}),
            attributes: {
                name: __('Название анализа'),
                laboratory_code: __('Код анализа лаборатории'),
                laboratory_id: __('Лаборатория'),
                disabled: {
                    label: __('Не использовать анализ при добавлении новых записей пациентов'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                clinics: {
                    label: __('Клиника'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
            },
        };
    },
};
</script>