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
            repository: new ActionLogRepository({filters: {agreement_price_act: this.id}}),
            attributes: {
                time: __('Время'),
                date: {
                    label: __('Дата'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                recommended: __('Стоимость рекомд.'),
                deleted: __('Удаленно'),
                date_from: __('Дата начала действия тарифа'),
                status: {
                    label: __('Статус акта'),
                    formatter: (val) => {
                        return this.$handbook.getOption('price_agreement_act_status', val);
                    }
                }
            },
        };
    },
};
</script>