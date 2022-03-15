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
            repository: new ActionLogRepository({filters: {transfer_list: this.id}}),
            attributes: {
                created: {
                    label: __('Создано'),
                   formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                sended: {
                    label: __('Отправленно'),
                   formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
               
            },
        };
    },
};
</script>