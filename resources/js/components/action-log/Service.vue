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
            repository: new ActionLogRepository({filters: {service: this.id}}),
            attributes: {
                name: __('Услуга'),
                name_ua: __('Название укр.'),
                specialization_id: __('Специализация'),
                disabled: {
                    label: __('Не использовать услугу при добавлении новых записей пациентов'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_for_discount_card: {
                    label: __('Услуга предназначена для оплаты дисконтной карты'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_base: {
                    label: __('Является базовой в курсе лечения'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                payment_destination_id: __('Назначение платежа'),
                clinics: {
                    label: __('Клиника'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                is_online: {
                    label: __('Онлайн видеоконсультация'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                site_service_type: {
                    label: __('Тип услуги на сайте'),
                    formatter: (val) => {
                        return this.$handbook.getOption('site_service_type', val);
                    }
                },
                for_foreigners: {
                    label: __('Услуга для иностранных граждан'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
            },
        };
    },
};
</script>
