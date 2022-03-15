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
            repository: new ActionLogRepository({
                filters: {
                    assigned_medicine: this.id,
                },
            }),
            attributes: {
                quantity: __('Количество'),
                medicine_id: __('Медикамент'),
                medication_duration: __('Длительность приёма'),
                clinic_id: __('Клиника'),
                appointment_id: __('Запись на прием'),
                assigner_id: __('Назначил'),
                comment: __('Комментарий'),
                card_specialization_id: __('Специализация карты'),
                issued: __('Выдано'),
                cost: {
                    label: __('Стоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                patient_id: __('Пациент'),
                self_cost: {
                    label: __('Себестоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                base_cost: {
                    label: __('Базовая стоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                medicine_type: {
                    formatter: (val) => {
                        return ''
                    }
                },
                id: {
                    formatter: (val) => {
                        return ''
                    }
                },
                is_free: {
                    label: __('Бесплатный'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                by_policy: {
                    label: __('По страховой'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                franchise: __('Франшиза, %'),
                warranter: __('Гарант'),
            },
        };
    },
};
</script>
