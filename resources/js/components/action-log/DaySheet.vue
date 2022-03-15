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
        category: {
            type: String,
        },
    },
    data() {
        return {
            repository: new ActionLogRepository({
                filters: {
                    day_sheet: this.id,
                    category: this.category,
                },
            }),
            attributes: {
                date: {
                    label: __('Дата'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                time: {
                    label: __('Время'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                clinic: __('Клиника'),
            },
        };
    },
};
</script>