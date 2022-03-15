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
            repository: new ActionLogRepository({filters: {site_enquiry: this.id}}),
            attributes: {
                operator_id: __('Оператор'),
                status: {
                    label: __('Статус'),
                    formatter: (value) => {
                        return this.$handbook.getOption('enquiry_status', value);
                    },
                },
            },
        };
    },
    methods: {
    }
};
</script>
