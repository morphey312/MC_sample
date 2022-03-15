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
            repository: new ActionLogRepository({filters: {group: this.id}}),
            attributes: {
                name: __('Название группы'),
            },
        };
    },
};
</script>
