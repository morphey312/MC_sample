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
            repository: new ActionLogRepository({filters: {appointment_service: this.id}}),
            attributes: {
                not_debt: {
                    label: __('Должник'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
            },
        };
    },
};
</script>
