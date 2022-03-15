<script>
import DefaultLog from '../../Default.vue';
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
            repository: new ActionLogRepository({filters: {patient_signed_uploads: this.id}}),
            attributes: {
                attachments: {
                    label: __('Файлы'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                card_specialization_id: __('Специализация карты'),
                name: __('Имя документа'),
            },
        };
    }
};
</script>
