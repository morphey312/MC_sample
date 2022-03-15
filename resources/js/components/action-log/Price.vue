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
                    price: this.id,
                    category: this.category,
                },
            }),
            attributes: {
                date_from: {
                    label: __('Дата начала'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_to: {
                    label: __('Дата окончания'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                cost: {
                    label: __('Стоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                self_cost: {
                    label: __('Себестоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                currency: {
                    label: __('Валюта'),
                    formatter: (val) => {
                        return this.$handbook.getOption('currency', val);
                    }
                },
                set_id: {
                    label: __('Набор цен'),
                    formatter: (val) => {
                        return this.$handbook.getOption('price_set', val);
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