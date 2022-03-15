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
            repository: new ActionLogRepository({filters: {assigned_medicine: this.id}}),
            attributes: {
                assigned_medicines: __('Назначены медикаменты'),
            },
        };
    },
};
</script>
