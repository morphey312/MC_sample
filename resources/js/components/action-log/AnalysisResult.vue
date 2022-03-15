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
            repository: new ActionLogRepository({filters: {analysis_result: this.id}}),
            attributes: {
                printed: {
                    label: __('Распечатано'),
                    formatter: (val) => {
                        return this.$formatter.boolToString(val);
                    }
                },
                downloaded: {
                    label: __('Загружено'),
                    formatter: (val) => {
                        return this.$formatter.boolToString(val);
                    }
                },
                quantity: __('Количество'),
                cost: {
                    label: __('Стоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                discount: {
                    label: __('Скидка'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                status: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('analysis_status', val);
                    }
                },
                date_expected_pass: {
                    label: __('Предполагаемая дата сдачи'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_pass: {
                    label: __('Дата сдачи'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_expected_ready: {
                    label: __('Предполагаемая дата готовности'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_ready: {
                    label: __('Дата готовности'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_sent_email: {
                    label: __('Дата отправки на email'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                delivery_status: {
                    label: __('Статус доставки'),
                    formatter: (val) => {
                        return this.$handbook.getOption('delivery_status', val);
                    }
                },
                warranter: {
                    label: __('Гарант'),
                },
                franchise: {
                    label: __('Франшиза'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                by_policy: {
                    label: __('Полис'),
                    formatter: (val) => {
                        return this.$formatter.boolToString(val);
                    }
                },
                attachments: {
                    label: __('Вложение'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                }
            },
        };
    },
};
</script>
